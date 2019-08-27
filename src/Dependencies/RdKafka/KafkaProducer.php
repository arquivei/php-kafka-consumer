<?php

namespace Arquivei\Kafka\Dependencies\RdKafka;

class KafkaProducer
{
    private $libProducer;
    private $topic;

    public function __construct($libProducer, $topicName)
    {
        $this->libProducer = $libProducer;
        $libTopic = $this->libProducer->newTopic($topicName);
        $this->topic = new KafkaTopic($libTopic);
    }

    public function getTopic() : KafkaTopic
    {
        return $this->topic;
    }
}