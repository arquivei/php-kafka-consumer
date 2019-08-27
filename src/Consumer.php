<?php

namespace Kafka\Consumer;

use Kafka\Consumer\Dependencies\RdKafka\Config\KafkaConfig;
use Kafka\Consumer\Dependencies\RdKafka\Config\KafkaSaslConfig;
use Kafka\Consumer\Dependencies\RdKafka\Config\KafkaTopicConfig;
use Kafka\Consumer\Log\Logger;
use Kafka\Consumer\Entities\Config;
use Arquivei\Kafka\Exceptions\KafkaConsumerException;

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
        $this->consumer->subscribe($this->config->getTopics());

        $this->commits = 0;
        do {
            $message = $this->consumer->consume(120000);
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
                    $this->logger->error($message, null, 'CONSUMER');
                    throw new KafkaConsumerException($message->errstr());
            }
        } while (!$this->isMaxMessage());
    }

    private function setConf(): \RdKafka\Conf
    {

        $kafkaConfig = new KafkaConfig();
        $kafkaConfig->setGroupId($this->config->getGroupId());
        $kafkaConfig->setBroker($this->config->getBroker());
        $kafkaConfig->setSecurityProtocol($this->config->getSecurityProtocol());
        $kafkaConfig->setTopicConfig(new KafkaTopicConfig());
        if ($kafkaConfig->isPlainText()) {
            $kafkaConfig->setSaslConfig (
                new KafkaSaslConfig(
                    $this->config->getSasl()->getUsername(),
                    $this->config->getSasl()->getPassword(),
                    $this->config->getSasl()->getMechanisms()
                )
            );
        }
        return $kafkaConfig->getKafkaExtensionInstance();
    }

    private function executeMessage(\RdKafka\Message $message): void
    {
        try {
            $this->config->getConsumer()->handle($message->payload);
            $success = true;
        } catch (\Throwable $throwable) {
            $this->logger->error($message, $throwable);
            $success = $this->handleException($throwable, $message);
        }

        $this->commit($message, $success);
    }

    private function handleException(
        \Throwable $exception,
        \RdKafka\Message $message
    ): bool {
        try {
            $this->config->getConsumer()->failed(
                $message->payload,
                $this->config->getTopics()[0],
                $exception
            );
            return true;
        } catch (\Throwable $throwable) {
            if ($exception !== $throwable) {
                $this->logger->error($message, $throwable, 'HANDLER_EXCEPTION');
            }
            return false;
        }
    }

    private function sendToDql(\RdKafka\Message $message): void
    {
        $topic = $this->producer->newTopic($this->config->getDlq());
        $topic->produce(
            RD_KAFKA_PARTITION_UA,
            0,
            $message->payload,
            $this->config->getConsumer()->producerKey($message->payload)
        );
    }

    private function commit(\RdKafka\Message $message, bool $success): void
    {
        try {
            if (!$success && !is_null($this->config->getDlq())) {
                $this->sendToDql($message);
                $this->commits = 0;
                $this->consumer->commit();
                return;
            }

            $this->commits++;
            if ($this->isMaxMessage() || $this->commits >= $this->config->getCommit()) {
                $this->commits = 0;
                $this->consumer->commit();
                return;
            }
        } catch (\Throwable $throwable) {
            $this->logger->error($message, $throwable, 'MESSAGE_COMMIT');
            if ($throwable->getCode() != RD_KAFKA_RESP_ERR__NO_OFFSET){
                throw $throwable;
            }
        }
    }

    private function isMaxMessage(): bool
    {
        return $this->messageNumber == $this->config->getMaxMessages();
    }
}
