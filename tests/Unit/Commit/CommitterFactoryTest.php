<?php

declare(strict_types=1);

namespace Kafka\Consumer\Tests\Unit\Commit;

use Kafka\Consumer\Commit\BatchCommitter;
use Kafka\Consumer\Commit\CommitterFactory;
use Kafka\Consumer\Commit\KafkaCommitter;
use Kafka\Consumer\Commit\NativeSleeper;
use Kafka\Consumer\Commit\RetryableCommitter;
use Kafka\Consumer\Commit\VoidCommitter;
use Kafka\Consumer\Contracts\Consumer;
use Kafka\Consumer\Entities\Config;
use Kafka\Consumer\MessageCounter;
use PHPUnit\Framework\TestCase;
use RdKafka\KafkaConsumer;

class CommitterFactoryTest extends TestCase
{
    public function testShouldBuildARetryableBatchCommitterWhenAutoCommitIsDisable(): void
    {
        $config = new Config(
            null,
            ['topic'],
            'broker',
            1,
            'group',
            $this->createMock(Consumer::class),
            'security',
            null
        );

        $consumer = $this->createMock(KafkaConsumer::class);

        $messageCounter = new MessageCounter(6);

        $factory = new CommitterFactory($messageCounter);

        $committer = $factory->make($consumer, $config);

        $expectedCommitter = new BatchCommitter(
            new RetryableCommitter(
                new KafkaCommitter(
                    $consumer
                ),
                new NativeSleeper(),
                $config->getMaxCommitRetries()
            ),
            $messageCounter,
            $config->getCommit()
        );

        $this->assertEquals($expectedCommitter, $committer);
    }

    public function testShouldBuildAVoidCommitterWhenAutoCommitIsEnabled(): void
    {
        $config = new Config(
            null,
            ['topic'],
            'broker',
            1,
            'group',
            $this->createMock(Consumer::class),
            'security',
            null,
            6,
            6,
            true
        );

        $consumer = $this->createMock(KafkaConsumer::class);

        $messageCounter = new MessageCounter(6);

        $factory = new CommitterFactory($messageCounter);

        $committer = $factory->make($consumer, $config);

        $this->assertInstanceOf(VoidCommitter::class, $committer);
    }
}
