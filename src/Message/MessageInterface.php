<?php

namespace Arquivei\Kafka\Message;

interface MessageInterface
{
    public function getPayload() : \stdClass;
}