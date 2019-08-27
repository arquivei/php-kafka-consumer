<?php

namespace Arquivei\Kafka\Consumer;

use Arquivei\Kafka\Consumer\Error\ErrorHandlerInterface;
use Arquivei\Kafka\Dependencies\RdKafka\KafkaConsumer;
use Arquivei\Kafka\Message\MessageFactory;
use Kafka\Consumer\Dependencies\RdKafka\Config\KafkaConfig;
use RdKafka\KafkaConsumer as LibConsumer;

class ConsumerFactory
{
    private const READ_BUFFER_SIZE = 120000;

    public static function createKafkaConsumer(
        KafkaConfig $kafkaConfig,
        MessageExecutorInterface $messageExecutor,
        ErrorHandlerInterface $errorHandler
    ) {
        $kafkaConsumer = new KafkaConsumer(
            new MessageFactory(),
            new LibConsumer($kafkaConfig->getKafkaExtensionInstance()),
            self::READ_BUFFER_SIZE
        );

        return new Consumer(
            $kafkaConsumer,
            $messageExecutor,
            $errorHandler
        );
    }
}