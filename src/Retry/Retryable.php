<?php

namespace Kafka\Consumer\Retry;

use Kafka\Consumer\Commit\Sleeper;
use RdKafka\Exception;

class Retryable
{
    private $sleeper;
    private $maximumRetries;
    private $retryableErrors;

    public function __construct(Sleeper $sleeper, int $maximumRetries, array $retryableErrors)
    {
        $this->sleeper = $sleeper;
        $this->maximumRetries = $maximumRetries;
        $this->retryableErrors = $retryableErrors;
    }

    public function retry(
        callable $function,
        int $currentRetries = 0,
        int $delayInSeconds = 1,
        bool $exponentially = true
    ) {
        try {
            $function();
        } catch (Exception $exception) {
            if (in_array($exception->getCode(), $this->retryableErrors) && $currentRetries < $this->maximumRetries) {
                $this->sleeper->sleep((int)($delayInSeconds * 1e6));
                $this->retry(
                    $function,
                    ++$currentRetries,
                    $exponentially == true ? $delayInSeconds * 2 : $delayInSeconds
                );
                return;
            }

            throw $exception;
        }
    }
}
