<?php

namespace Arquivei\Kafka\Dependencies\RdKafka;

class KafkaMessage
{
    const NO_ERROR = RD_KAFKA_RESP_ERR_NO_ERROR;
    const ERROR_PARTITION_EOF = RD_KAFKA_RESP_ERR__PARTITION_EOF;
    const ERROR_TIMED_OUT = RD_KAFKA_RESP_ERR__TIMED_OUT;

    private $libMessage;
    public function __construct($message)
    {
        $this->libMessage = $message;
    }

    public function getPayload() : string
    {
        return $this->libMessage->payload;
    }

    public function getError()
    {
        return $this->libMessage->err;
    }

    public function getErrorMessage() : string
    {
        return $this->libMessage->errstr();
    }
}