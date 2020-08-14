<?php

namespace Kafka\Consumer\Validators\Commands\PhpKafkaConsumer;

use Kafka\Consumer\Contracts\Consumer;
use Kafka\Consumer\Exceptions\InvalidCommitException;
use Kafka\Consumer\Exceptions\InvalidConsumerException;

class Validator
{
    public function validateOptions(array $options): void
    {
        $this->validateCommit($options['commit']);
        $this->validateConsumer($options['consumer']);
    }

    private function validateCommit(?string $commit): void
    {
        if (is_null($commit) || $commit < 1) {
            throw new InvalidCommitException();
        }
    }

    private function validateConsumer(?string $consumer): void
    {
        if (! class_exists($consumer) || !is_subclass_of($consumer, Consumer::class)) {
            throw new InvalidConsumerException();
        }
    }
}
