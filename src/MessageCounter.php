<?php

declare(strict_types=1);

namespace Kafka\Consumer;

class MessageCounter
{
    private $messageCount = 0;
    private $maxMessages;

    public function __construct(int $maxMessages)
    {
        $this->maxMessages = $maxMessages;
    }

    public function add(): void
    {
        $this->messageCount++;
    }

    public function isMaxMessage(): bool
    {
        return $this->messageCount === $this->maxMessages;
    }
}
