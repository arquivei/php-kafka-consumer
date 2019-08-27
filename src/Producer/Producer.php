<?php

namespace Arquivei\Kafka\Producer;

use Arquivei\Kafka\Dependencies\RdKafka\KafkaProducer;
use Arquivei\Kafka\Message\MessageInterface;

class Producer implements ProducerInterface
{
    private $kafkaProducer;
    public function __construct(KafkaProducer $kafkaProducer)
    {
        $this->kafkaProducer = $kafkaProducer;
    }

    public function produce(MessageInterface $message)
    {
        $payload = json_encode($message->getPayload());
        $topic = $this->kafkaProducer->getTopic();
        $topic->produce($payload);
    }
}