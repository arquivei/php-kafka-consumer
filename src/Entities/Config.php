<?php

namespace Kafka\Consumer\Entities;

use Kafka\Consumer\Contracts\Consumer;
use Kafka\Consumer\Entities\Config\Sasl;

class Config
{
    private $dlq;
    private $sasl;
    private $topics;
    private $broker;
    private $commit;
    private $groupId;
    private $consumer;
    private $maxMessages;
    private $securityProtocol;
    private $maxCommitRetries;
    private $autoCommit;
    private $customOptions;

    public function __construct(
        ?Sasl $sasl,
        array $topics,
        string $broker,
        int $commit,
        string $groupId,
        Consumer $consumer,
        string $securityProtocol,
        ?string $dlq,
        int $maxMessages = -1,
        int $maxCommitRetries = 6,
        bool $autoCommit = false,
        array $customOptions = []
    ) {
        $this->dlq = $dlq;
        $this->sasl = $sasl;
        $this->topics = $topics;
        $this->broker = $broker;
        $this->commit = $commit;
        $this->groupId = $groupId;
        $this->consumer = $consumer;
        $this->maxMessages = $maxMessages;
        $this->securityProtocol = $securityProtocol;
        $this->maxCommitRetries = $maxCommitRetries;
        $this->autoCommit = $autoCommit;
        $this->customOptions = $customOptions;
    }

    public function getCommit(): int
    {
        return $this->commit;
    }

    public function getMaxCommitRetries(): int
    {
        return $this->maxCommitRetries;
    }

    public function getTopics(): array
    {
        return $this->topics;
    }

    public function getConsumer(): Consumer
    {
        return $this->consumer;
    }

    public function getDlq(): ?string
    {
        return $this->dlq;
    }

    public function getMaxMessages(): int
    {
        return $this->maxMessages;
    }

    public function isAutoCommit(): bool
    {
        return $this->autoCommit;
    }

    public function getConsumerOptions(): array
    {
        $options = [
            'auto.offset.reset' => 'smallest',
            'queued.max.messages.kbytes' => '10000',
            'enable.auto.commit' => 'false',
            'compression.codec' => 'gzip',
            'max.poll.interval.ms' => '86400000',
            'group.id' => $this->groupId,
            'bootstrap.servers' => $this->broker,
            'security.protocol' => $this->securityProtocol,
        ];

        if ($this->autoCommit) {
            $options['enable.auto.commit'] = 'true';
        }

        return array_merge($options, $this->getSaslOptions(), $this->customOptions);
    }

    public function getProducerOptions(): array
    {
        $config = [
            'compression.codec' => 'gzip',
            'bootstrap.servers' => $this->broker,
            'security.protocol' => $this->securityProtocol,
        ];

        return array_merge($config, $this->getSaslOptions());
    }

    private function getSaslOptions(): array
    {
        if ($this->isPlainText() && $this->sasl !== null) {
            return [
                'sasl.username' => $this->sasl->getUsername(),
                'sasl.password' => $this->sasl->getPassword(),
                'sasl.mechanisms' => $this->sasl->getMechanisms(),
            ];
        }

        return [];
    }

    private function isPlainText(): bool
    {
        return $this->securityProtocol == 'SASL_PLAINTEXT';
    }
}
