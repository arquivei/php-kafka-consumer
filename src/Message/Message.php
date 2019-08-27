<?php

namespace Arquivei\Kafka\Message;

use Arquivei\Kafka\Dependencies\RdKafka\KafkaMessage;

class Message implements MessageInterface
{
    private $payload;
    public function __construct(KafkaMessage $message)
    {
        $this->payload = json_decode($message->getPayload());
    }

    public function getPayload(): \stdClass
    {
        return $this->payload;
    }
}