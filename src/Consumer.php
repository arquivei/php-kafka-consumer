<?php

namespace Kafka\Consumer;

use Kafka\Consumer\Log\Logger;
use Kafka\Consumer\Entities\Config;
use Kafka\Consumer\Exceptions\KafkaConsumerException;

class Consumer
{
    private $config;
    private $logger;
    private $commits;
    private $consumer;
    private $producer;
    private $messageNumber = 0;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->logger = new Logger();
    }

    public function consume(): void
    {
        $this->consumer = new \RdKafka\KafkaConsumer($this->setConf());
        $this->producer = new \RdKafka\Producer($this->setConf());
        $this->consumer->subscribe([$this->config->getTopic()]);

        $this->commits = 0;
        do {
            $message = $this->consumer->consume(500);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $this->messageNumber++;
                    $this->executeMessage($message);
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    // NO MESSAGE
                    break;
                default:
                    // ERROR
                    throw new KafkaConsumerException($message->errstr());
                    break;
            }
        } while (!$this->isMaxMessage());

    }

    private function setConf(): \RdKafka\Conf
    {
        $topicConf = new \RdKafka\TopicConf();
        $topicConf->set('auto.offset.reset', 'smallest');

        $conf = new \RdKafka\Conf();
        $conf->set('enable.auto.commit', 'false');
        $conf->set('group.id', $this->config->getGroupId());
        $conf->set('bootstrap.servers', $this->config->getBroker());
        $conf->set('security.protocol', $this->config->getSecurityProtocol());
        $conf->setDefaultTopicConf($topicConf);

        if ($this->config->isPlainText()) {
            $conf->set('sasl.username', $this->config->getSasl()->getUsername());
            $conf->set('sasl.password', $this->config->getSasl()->getPassword());
            $conf->set('sasl.mechanisms', $this->config->getSasl()->getMechanisms());
        }

        return $conf;
    }

    private function executeMessage(\RdKafka\Message $message): void
    {
        $attempts = 1;
        do {
            try {
                $this->config->getConsumer()->handle($message->payload);
                $success = true;
                $this->commit($message, $attempts, true);
            } catch (\Throwable $exception) {
                $this->logger->error($message->offset, $attempts, $exception);

                $success = $this->isMaxAttemptReached($message, $attempts);
                $attempts++;
            }
        } while (!$success);
    }

    private function commit(\RdKafka\Message $message, int $attempts, bool $success): void
    {
        try {
            if (!$success && !is_null($this->config->getDlq())) {
                $this->sendToDql($message);
                $this->consumer->commit();
                $this->commits = 0;
                return;
            }

            $this->commits++;
            if ($this->commits >= $this->config->getCommit()) {
                $this->consumer->commit();
                $this->commits = 0;
                return;
            }
        } catch (\Throwable $throwable) {
            $offset = property_exists($message, 'offset') ? $message->offset : null;
            $this->logger->error($offset, $attempts, $throwable);
            if ($throwable->getCode() != RD_KAFKA_RESP_ERR__NO_OFFSET) {
                throw $throwable;
            }
        }
    }

    private function sendToDql(\RdKafka\Message $message): void
    {
        $topic = $this->producer->newTopic($this->config->getDlq());
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message->payload);
    }

    private function isMaxAttemptReached(\RdKafka\Message $message, int $attempts): bool
    {
        if (
            $this->config->getMaxAttempts()->hasMaxAttempts() &&
            $this->config->getMaxAttempts()->hasReachedMaxAttempts($attempts)
        ) {
            $this->commit($message, $attempts, false);
            return true;
        }

        $this->config->getSleep()->waiting();

        return false;
    }

    private function isMaxMessage(): bool
    {
        return $this->messageNumber == $this->config->getMaxMessages();
    }
}
