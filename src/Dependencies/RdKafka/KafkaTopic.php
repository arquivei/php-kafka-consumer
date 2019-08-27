<?php

namespace Arquivei\Kafka\Dependencies\RdKafka;

class KafkaTopic
{
    const UNDEFINED_PARTITION = RD_KAFKA_PARTITION_UA;
    private $libTopic;

    public function __construct($libTopic)
    {
        $this->libTopic = $libTopic;
    }

    public function produce($payload, $partition = self::UNDEFINED_PARTITION, $producerKey = null)
    {
        $this->libTopic->produce($partition, 0, $payload, $producerKey);
    }
}