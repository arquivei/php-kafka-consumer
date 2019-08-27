<?php

namespace Arquivei\Kafka\Consumer;

use Arquivei\Kafka\Message\MessageInterface;

interface MessageExecutorInterface
{
    /**
     * Receives a message interfaces, processes
     *
     * @param MessageInterface $message
     */
    public function execute(MessageInterface $message) : void;
}