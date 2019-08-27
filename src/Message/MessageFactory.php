<?php

namespace Arquivei\Kafka\Message;

use Arquivei\Kafka\Dependencies\RdKafka\KafkaMessage;

class MessageFactory
{
    public function creteFromKafkaMessage(KafkaMessage $kafkaMessage) : MessageInterface
    {
        return new Message($kafkaMessage);
    }
}