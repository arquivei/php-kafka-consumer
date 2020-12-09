<?php

declare(strict_types=1);

namespace Kafka\Consumer\Log;

use Kafka\Consumer\Events\FailedToCommitMessage;
use Kafka\Consumer\Events\FailedToConsumeMessage;
use Kafka\Consumer\Events\FailedToHandleException;
use Kafka\Consumer\Events\FailedToHandleMessage;

class LoggingEventSubscriber
{
    private $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    public function failedToHandleMessage(FailedToHandleMessage $event): void
    {
        $this->logger->error($event->getRawMessage(), $event->getCause());
    }

    public function failedToConsumeMessage(FailedToConsumeMessage $event): void
    {
        $this->logger->error($event->getRawMessage(), $event->getCause(), 'CONSUMER');
    }

    public function failedToCommitMessage(FailedToCommitMessage $event): void
    {
        $this->logger->error($event->getRawMessage(), $event->getCause(), 'MESSAGE_COMMIT');
    }

    public function failedToHandleException(FailedToHandleException $event): void
    {
        $this->logger->error($event->getRawMessage(), $event->getCause(), 'HANDLER_EXCEPTION');
    }
}
