<?php

namespace Kafka\Consumer;

use Kafka\Consumer\Commit\CommitterBuilder;
use Kafka\Consumer\Commit\NativeSleeper;
use Kafka\Consumer\Entities\Config;
use Kafka\Consumer\Events\EventDispatcher;
use Kafka\Consumer\Events\MessageHandled;
use Kafka\Consumer\Exceptions\KafkaConsumerException;
use Kafka\Consumer\Log\Logger;
use Kafka\Consumer\Retry\Retryable;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use RdKafka\Message;
use RdKafka\Producer;
use Throwable;

class Consumer
{
    private const IGNORABLE_CONSUME_ERRORS = [
        RD_KAFKA_RESP_ERR__PARTITION_EOF,
        RD_KAFKA_RESP_ERR__TRANSPORT,
        RD_KAFKA_RESP_ERR_NO_ERROR,
        RD_KAFKA_RESP_ERR__TIMED_OUT,
    ];

    private const TIMEOUT_ERRORS = [
        RD_KAFKA_RESP_ERR_REQUEST_TIMED_OUT,
    ];

    private const IGNORABLE_COMMIT_ERRORS = [
        RD_KAFKA_RESP_ERR__NO_OFFSET,
    ];

    private $config;
    private $logger;
    private $consumer;
    private $producer;
    private $messageCounter;
    private $committer;
    private $retryable;
    private $eventDispatcher;

    public function __construct(Config $config, EventDispatcher $eventDispatcher = null)
    {
        $this->config = $config;
        $this->logger = new Logger();
        $this->messageCounter = new MessageCounter($config->getMaxMessages());
        $this->retryable = new Retryable(new NativeSleeper(), 6, self::TIMEOUT_ERRORS);
        $this->eventDispatcher = $eventDispatcher ?? new EventDispatcher();
    }

    public function consume(): void
    {
        $this->consumer = new KafkaConsumer($this->setConf());
        $this->producer = new Producer($this->setConf());

        $this->committer = CommitterBuilder::withConsumer($this->consumer)
            ->andRetry(new NativeSleeper(), $this->config->getMaxCommitRetries())
            ->committingInBatches($this->messageCounter, $this->config->getCommit())
            ->build();

        $this->consumer->subscribe($this->config->getTopics());

        do {
            $message = $this->doConsume();
            if (RD_KAFKA_RESP_ERR_NO_ERROR !== $message->err) {
                continue;
            }

            try {
                $messageHandledEvent = new MessageHandled($message->payload);
                $this->handleMessage($message);
                $this->eventDispatcher->dispatch($messageHandledEvent);

                if ($messageHandledEvent->shouldStopExecution()) {
                    break;
                }
            } catch (Throwable $exception) {
                $this->logger->error($message, $exception);
                $this->handleException($exception, $message);
            }
        } while (!$this->isMaxMessage());
    }

    private function setConf(): Conf
    {
        $conf = new Conf();
        $conf->set('auto.offset.reset', 'smallest');
        $conf->set('queued.max.messages.kbytes', '10000');
        $conf->set('enable.auto.commit', 'false');
        $conf->set('compression.codec', 'gzip');
        $conf->set('max.poll.interval.ms', '86400000');
        $conf->set('group.id', $this->config->getGroupId());
        $conf->set('bootstrap.servers', $this->config->getBroker());
        $conf->set('security.protocol', $this->config->getSecurityProtocol());

        if ($this->config->isPlainText() && $this->config->getSasl() !== null) {
            $conf->set('sasl.username', $this->config->getSasl()->getUsername());
            $conf->set('sasl.password', $this->config->getSasl()->getPassword());
            $conf->set('sasl.mechanisms', $this->config->getSasl()->getMechanisms());
        }

        return $conf;
    }

    private function doConsume(): Message
    {
        return $this->retryable->retry(
            function (): Message {
                $message = $this->consumer->consume(120000);

                if (!in_array($message->err, self::IGNORABLE_CONSUME_ERRORS)) {
                    $this->logger->error($message, null, 'CONSUMER');
                    throw new KafkaConsumerException($message->errstr(), $message->err);
                }

                return $message;
            }
        );
    }

    private function handleMessage(Message $message): void
    {
        $this->messageCounter->add();

        $this->config->getConsumer()->handle($message->payload);
        $this->commitMessage($message);
    }

    private function commitMessage(Message $message): void
    {
        try {
            $this->committer->commitMessage();
        } catch (Throwable $throwable) {
            if (!in_array($throwable->getCode(), self::IGNORABLE_COMMIT_ERRORS)) {
                $this->logger->error($message, $throwable, 'MESSAGE_COMMIT');
                throw $throwable;
            }
        }
    }

    private function handleException(
        Throwable $exception,
        Message $message
    ): void {
        try {
            $this->config->getConsumer()->failed(
                $message->payload,
                $this->config->getTopics()[0],
                $exception
            );
        } catch (Throwable $throwable) {
            if ($exception !== $throwable) {
                $this->logger->error($message, $throwable, 'HANDLER_EXCEPTION');
            }

            if (!is_null($this->config->getDlq())) {
                $this->sendToDlq($message);
                $this->commitDlq($message);
            }
        }
    }

    private function sendToDlq(Message $message): void
    {
        $topic = $this->producer->newTopic($this->config->getDlq());
        $topic->produce(
            RD_KAFKA_PARTITION_UA,
            0,
            $message->payload,
            $this->config->getConsumer()->producerKey($message->payload)
        );

        if (method_exists($this->producer, 'flush')) {
            $this->producer->flush(12000);
        }
    }

    private function commitDlq(Message $message): void
    {
        try {
            $this->committer->commitDlq();
        } catch (Throwable $throwable) {
            if (!in_array($throwable->getCode(), self::IGNORABLE_COMMIT_ERRORS)) {
                $this->logger->error($message, $throwable, 'MESSAGE_COMMIT');
                throw $throwable;
            }
        }
    }

    private function isMaxMessage(): bool
    {
        return $this->messageCounter->isMaxMessage();
    }
}
