<?php

namespace Kafka\Consumer\Laravel\Console\Commands;

use Illuminate\Console\Command;
use Kafka\Consumer\Contracts\Consumer;
use Kafka\Consumer\Dependencies\RdKafka\Config\KafkaConfig;
use Kafka\Consumer\Dependencies\RdKafka\Config\KafkaSaslConfig;
use Kafka\Consumer\Dependencies\RdKafka\Config\KafkaTopicConfig;
use Kafka\Consumer\Exceptions\InvalidCommitException;
use Kafka\Consumer\Exceptions\InvalidConsumerException;

class PhpKafkaConsumerCommand extends Command
{
    protected $signature = 'arquivei:php-kafka-consumer {--topic=*} {--consumer=} {--groupId=} {--commit=} {--dlq=} {--maxMessage=}';
    protected $description = 'A Kafka Worker in PHP';

    private $dlq;
    private $topics;
    private $config;
    private $groupId;
    private $maxMessage;

    public function __construct()
    {
        parent::__construct();
        $this->config = config('php-kafka-consumer');
    }

    public function handle()
    {
        $consumer = $this->option('consumer');
        $commit = $this->option('commit');
        $this->validateConsumer($consumer);
        $this->validateCommit($commit);

        $this->dlq = $this->option('dlq');
        $this->topics = $this->option('topic');
        $this->groupId = $this->option('groupId');
        $this->maxMessage = (int)$this->option('maxMessage');

        $config = new \Kafka\Consumer\Entities\Config(
            new \Kafka\Consumer\Entities\Config\Sasl(
                $this->config['sasl']['username'],
                $this->config['sasl']['password'],
                $this->config['sasl']['mechanisms']
            ),
            $this->getTopics(),
            $this->config['broker'],
            $commit,
            $this->getGroupId(),
            new $consumer(),
            $this->config['securityProtocol'],
            $this->getDlq(),
            $this->getMaxMessage()
        );

        (new \Kafka\Consumer\Consumer($config))->consume();
    }

    private function setConf(): \RdKafka\Conf
    {

        $kafkaConfig = new KafkaConfig();
        $kafkaConfig->setGroupId($this->config->getGroupId());
        $kafkaConfig->setBroker($this->config->getBroker());
        $kafkaConfig->setSecurityProtocol($this->config->getSecurityProtocol());
        $kafkaConfig->setTopicConfig(new KafkaTopicConfig());
        if ($kafkaConfig->isPlainText()) {
            $kafkaConfig->setSaslConfig (
                new KafkaSaslConfig(
                    $this->config->getSasl()->getUsername(),
                    $this->config->getSasl()->getPassword(),
                    $this->config->getSasl()->getMechanisms()
                )
            );
        }
        return $kafkaConfig->getKafkaExtensionInstance();
    }

    private function getTopics(): array
    {
        return (is_array($this->topics) && !empty($this->topics)) ? $this->topics : [];
    }

    private function getGroupId(): string
    {
        return (is_string($this->groupId) && strlen($this->groupId) > 1) ? $this->groupId : $this->config['groupId'];
    }

    private function getMaxMessage(): int
    {
        return (is_int($this->maxMessage) && $this->maxMessage >= 1) ? $this->maxMessage : -1;
    }

    private function getDlq(): ?string
    {
        return (is_string($this->dlq) && strlen($this->dlq) > 1) ? $this->dlq : null;
    }

    private function validateCommit(?int $commit): void
    {
        if (is_null($commit) || $commit < 1) {
            throw new InvalidCommitException();
        }
    }

    private function validateConsumer(?string $consumer): void
    {
        if (! class_exists($consumer) || !is_subclass_of($consumer, Consumer::class)) {
            throw new InvalidConsumerException();
        }
    }
}

