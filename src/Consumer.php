<?php

namespace Kafka\Consumer;

use Kafka\Consumer\Commit\CommitterBuilder;
use Kafka\Consumer\Commit\NativeSleeper;
use Kafka\Consumer\Log\Logger;
use Kafka\Consumer\Entities\Config;
use Kafka\Consumer\Exceptions\KafkaConsumerException;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use RdKafka\Message;
use RdKafka\Producer;
use RdKafka\TopicConf;
use Throwable;

class Consumer
{
    private const MAX_COMMIT_RETRIES = 6;

    private const IGNORABLE_CONSUME_ERRORS = [
        RD_KAFKA_RESP_ERR__PARTITION_EOF,
        RD_KAFKA_RESP_ERR__TIMED_OUT,
        RD_KAFKA_RESP_ERR_REQUEST_TIMED_OUT,
        RD_KAFKA_RESP_ERR__TRANSPORT,
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

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->logger = new Logger();
        $this->messageCounter = new MessageCounter($config->getMaxMessages());
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
            $message = $this->consumer->consume(120000);
            $this->handleMessage($message);
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

        if ($this->config->isPlainText()) {
            $conf->set('sasl.username', $this->config->getSasl()->getUsername());
            $conf->set('sasl.password', $this->config->getSasl()->getPassword());
            $conf->set('sasl.mechanisms', $this->config->getSasl()->getMechanisms());
        }

        return $conf;
    }

    private function executeMessage(Message $message): void
    {
        try {
            $this->config->getConsumer()->handle($message->payload);
            $success = true;
        } catch (Throwable $throwable) {
            $this->logger->error($message, $throwable);
            $success = $this->handleException($throwable, $message);
        }

        $this->commit($message, $success);
    }

    private function handleException(
        Throwable $exception,
        Message $message
    ): bool {
        try {
            $this->config->getConsumer()->failed(
                $message->payload,
                $this->config->getTopics()[0],
                $exception
            );
            return true;
        } catch (Throwable $throwable) {
            if ($exception !== $throwable) {
                $this->logger->error($message, $throwable, 'HANDLER_EXCEPTION');
            }
            return false;
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

    private function commit(Message $message, bool $success): void
    {
        try {
            if (!$success && !is_null($this->config->getDlq())) {
                $this->sendToDlq($message);
                $this->committer->commitDlq();
                return;
            }

            $this->committer->commitMessage();
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

    private function handleMessage(Message $message): void
    {
        if (RD_KAFKA_RESP_ERR_NO_ERROR === $message->err) {
            $this->messageCounter->add();
            $this->executeMessage($message);
            return;
        }

        if (!in_array($message->err, self::IGNORABLE_CONSUME_ERRORS)) {
            $this->logger->error($message, null, 'CONSUMER');
            throw new KafkaConsumerException($message->errstr(), $message->err);
        }
    }
}
