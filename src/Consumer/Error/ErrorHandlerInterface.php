<?php

namespace Arquivei\Kafka\Consumer\Error;

use Arquivei\Kafka\Message\MessageInterface;

interface ErrorHandlerInterface
{
    /**
     * Handle exceptions occurred during the consumption or message execution
     *
     * @param MessageInterface $message
     * @param \Throwable $throwable
     * @throws \Throwable
     */
    public function handle(MessageInterface $message, ?\Throwable $throwable) : void;
}