<?php

namespace Arquivei\Kafka\Consumer;

use Arquivei\Kafka\Consumer\Error\ErrorHandlerInterface;
use Arquivei\Kafka\Dependencies\RdKafka\KafkaConsumer;
use Arquivei\Kafka\Message\MessageInterface;

class Consumer implements ConsumerInterface
{
    private $kafkaConsumer;
    private $messageExecutor;
    private $errorHandler;

    public function __construct(
        KafkaConsumer $kafkaConsumer,
        MessageExecutorInterface $messageExecutor,
        ErrorHandlerInterface $errorHandler
    ) {
        $this->kafkaConsumer = $kafkaConsumer;
        $this->messageExecutor = $messageExecutor;
        $this->errorHandler = $errorHandler;
    }

    public function consume() : ?MessageInterface
    {
        $message = $this->kafkaConsumer->consume();
        if ($message) {
            try {
                $this->messageExecutor->execute($message);
            } catch (\Throwable $throwable) {
                $this->errorHandler->handle($message, $throwable);
            }
        }
        return $message;
    }

    public function commit(): void
    {
        $this->kafkaConsumer->commit();
    }
}