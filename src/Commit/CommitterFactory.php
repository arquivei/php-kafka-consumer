<?php

declare(strict_types=1);

namespace Kafka\Consumer\Commit;

use Kafka\Consumer\Entities\Config;
use Kafka\Consumer\MessageCounter;
use RdKafka\KafkaConsumer;

class CommitterFactory
{
    private $messageCounter;

    public function __construct(
        MessageCounter $messageCounter
    ) {
        $this->messageCounter = $messageCounter;
    }

    public function make(KafkaConsumer $kafkaConsumer, Config $config): Committer
    {
        if ($config->isAutoCommit()) {
            return new VoidCommitter();
        }

        return new BatchCommitter(
            new RetryableCommitter(
                new KafkaCommitter(
                    $kafkaConsumer
                ),
                new NativeSleeper(),
                $config->getMaxCommitRetries()
            ),
            $this->messageCounter,
            $config->getCommit()
        );
    }
}
