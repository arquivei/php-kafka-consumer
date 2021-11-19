<?php

namespace Kafka\Consumer\Laravel\Console\Commands\PhpKafkaConsumer;

class Options
{
    private $topics;
    private $consumer;
    private $groupId;
    private $commit;
    private $dlq;
    private $maxMessage;

    public function __construct(array $options)
    {
        if (is_string($options['topic'])) {
            $options['topic'] = [$options['topic']];
        }

        $this->topics = $options['topic'];
        $this->consumer = $options['consumer'];
        $this->groupId = $options['groupId'];
        $this->commit = $options['commit'];
        $this->dlq = $options['dlq'];
        $this->maxMessage = $options['maxMessage'];
    }

    public function getTopics(): array
    {
        return (is_array($this->topics) && !empty($this->topics)) ? $this->topics : [];
    }

    public function getConsumer(): ?string
    {
        return $this->consumer;
    }

    public function getGroupId(): string
    {
        return $this->groupId;
    }

    public function getCommit(): ?string
    {
        return $this->commit;
    }

    public function getDlq(): ?string
    {
        return (is_string($this->dlq) && strlen($this->dlq) > 1) ? $this->dlq : null;
    }

    public function getMaxMessage(): int
    {
        return (is_int($this->maxMessage) && $this->maxMessage >= 1) ? $this->maxMessage : -1;
    }
}
