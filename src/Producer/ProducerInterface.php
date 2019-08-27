<?php

namespace Arquivei\Kafka\Producer;

use Arquivei\Kafka\Message\MessageInterface;

interface ProducerInterface
{
    public function produce(MessageInterface $message);
}