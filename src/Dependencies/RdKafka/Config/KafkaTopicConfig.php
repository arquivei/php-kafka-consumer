<?php

namespace Kafka\Consumer\Dependencies\RdKafka\Config;

class KafkaTopicConfig
{
    private $config;

    public function __construct()
    {
        $this->config = new TopicConf();
        $this->config->set('auto.offset.reset', 'smallest');
    }

    public function getKafkaExtensionInstance()
    {
        return $this->config;
    }

}