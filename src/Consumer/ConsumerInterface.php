<?php

namespace Arquivei\Kafka\Consumer;

use Arquivei\Kafka\Message\MessageInterface;

interface ConsumerInterface
{
    /**
     * Retrieves a Kafka message, sends to message executor and handles errors
     *
     * @return \Arquivei\Kafka\Message\MessageInterface Consumed message
     * @throws \Arquivei\Kafka\Exceptions\KafkaConsumerException
     */
    public function consume() : ?MessageInterface;

    /**
     * Commits the offset to kafka
     */
    public function commit() : void ;
}