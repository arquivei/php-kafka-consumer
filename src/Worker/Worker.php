<?php

namespace Arquivei\Kafka\Worker;

use Arquivei\Kafka\Consumer\ConsumerFactory;
use Arquivei\Kafka\Consumer\Error\ErrorHandlerInterface;
use Arquivei\Kafka\Consumer\MessageExecutorInterface;
use Kafka\Consumer\Dependencies\RdKafka\Config\KafkaConfig;
use Kafka\Consumer\Dependencies\RdKafka\Config\KafkaSaslConfig;
use Kafka\Consumer\Dependencies\RdKafka\Config\KafkaTopicConfig;

class Worker
{
    private $commitInterval;
    private $consumer;

    public function __construct(
        KafkaConfig $config,
        MessageExecutorInterface $messageExecutor,
        int $commitInterval,
        ErrorHandlerInterface $errorHandler = null
    ) {
        $this->commitInterval = $commitInterval;
        $this->consumer = ConsumerFactory::createKafkaConsumer($config, $messageExecutor, $errorHandler);
    }

    public function run()
    {
        while (true) {
            $consumedMessagesCount = 0;
            try {
                while ($consumedMessagesCount < $this->commitInterval) {
                    $consumedMessagesCount++;
                    $this->consumer->consume();
                }
                $this->consumer->commit();
            } catch (\Throwable $throwable) {
                $errorCode = $throwable->getCode();
                if ($errorCode != RD_KAFKA_RESP_ERR__NO_OFFSET && $errorCode != RD_KAFKA_RESP_ERR_REQUEST_TIMED_OUT) {

                }
            }
        }
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

}