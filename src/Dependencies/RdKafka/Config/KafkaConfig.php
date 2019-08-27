<?php


namespace Kafka\Consumer\Dependencies\RdKafka\Config;

use RdKafka\TopicConf;
use RdKafka\Conf;

class KafkaConfig
{
    private $config;
    public function __construct() {
        $this->config = new Conf();
        $this->setKafkaDefaults();

        if ($this->config->isPlainText()) {
            new KafkaSaslConfig(
                $this->config->getSasl()->getUsername(),
                $this->config->getSasl()->getPassword(),
                $this->config->getSasl()->getMechanisms()
            );

            $this->config->set('sasl.username', $this->config->getSasl()->getUsername());
            $this->config->set('sasl.password', $this->config->getSasl()->getPassword());
            $this->config->set('sasl.mechanisms', $this->config->getSasl()->getMechanisms());
        }
    }

    private function setKafkaDefaults()
    {
        $this->config->set('enable.auto.commit', 'false');
        $this->config->set('compression.codec', 'gzip');
        $this->config->set('max.poll.interval.ms', '86400000');
    }

    public function isPlainText() : bool
    {
        return $this->config->isPlainText();
    }

    public function setTopicConfig(KafkaTopicConfig $topicConfig)
    {
        $this->config->setDefaultTopicConf($topicConfig->getKafkaExtensionInstance());
    }

    public function setSaslConfig(KafkaSaslConfig $salsConfig)
    {
        $this->config->set('sasl.username', $salsConfig->getUsername());
        $this->config->set('sasl.password', $salsConfig->getPassword());
        $this->config->set('sasl.mechanisms', $salsConfig->getMechanisms());
    }

    public function setGroupId($groupId) : KafkaConfig
    {
        $this->config->set('group.id', $groupId);
    }

    public function setBroker($broker) : KafkaConfig
    {
        $this->config->set('bootstrap.servers', $broker);
    }

    public function setSecurityProtocol($securityProtocol) : KafkaConfig
    {
        $this->config->set('security.protocol', $securityProtocol);
    }

    public function getKafkaExtensionInstance()
    {
        return $this->config;
    }
}