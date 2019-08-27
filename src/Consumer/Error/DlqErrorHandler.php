<?php

namespace Arquivei\Kafka\Consumer\Error;

use Arquivei\Kafka\Message\MessageInterface;
use Arquivei\Kafka\Producer\ProducerInterface;

class DlqErrorHandler implements ErrorHandlerInterface
{
    private $dlqProducer;
    public function __construct(ProducerInterface $dlqProducer)
    {
        $this->dlqProducer = $dlqProducer;
    }

    public function handle(MessageInterface $message, ?\Throwable $throwable): void
    {
        $this->dlqProducer->produce($message);
    }
}