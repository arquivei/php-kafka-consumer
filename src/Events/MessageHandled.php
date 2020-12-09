<?php

declare(strict_types=1);

namespace Kafka\Consumer\Events;

class MessageHandled
{
    private $rawMessage;
    private $shouldStopExecution = false;

    public function __construct(string $rawMessage)
    {
        $this->rawMessage = $rawMessage;
    }

    public function getRawMessage(): string
    {
        return $this->rawMessage;
    }

    public function stopExecution(): void
    {
        $this->shouldStopExecution = true;
    }

    public function shouldStopExecution(): bool
    {
        return $this->shouldStopExecution;
    }
}
