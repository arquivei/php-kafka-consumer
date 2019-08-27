<?php

namespace Arquivei\Kafka\Exceptions;

use Throwable;

class KafkaConsumerException extends \Exception
{
    public function __construct(
        string $message = 'Error while consuming kafka topic',
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
