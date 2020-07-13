<?php

declare(strict_types=1);

namespace Kafka\Consumer\Commit;

use RdKafka\KafkaConsumer;

/**
 * Kafka committer
 *
 * It commits the offsets of the consumer
 */
class KafkaCommitter implements Committer
{
    private $consumer;

    public function __construct(KafkaConsumer $consumer)
    {
        $this->consumer = $consumer;
    }

    public function commitMessage(): void
    {
        $this->consumer->commit();
    }

    public function commitDlq(): void
    {
        $this->consumer->commit();
    }
}
