<?php

declare(strict_types=1);

namespace Kafka\Consumer\Tests\Unit\Commit;

use Kafka\Consumer\Commit\BatchCommitter;
use Kafka\Consumer\Commit\Committer;
use Kafka\Consumer\MessageCounter;
use PHPUnit\Framework\TestCase;

class BatchCommitterTest extends TestCase
{
    public function testShouldCommitMessageOnlyAfterTheBatchSizeIsReached()
    {
        $committer = $this->createMock(Committer::class);
        $committer
            ->expects($this->exactly(2))
            ->method('commitMessage');

        $batchSize = 3;
        $messageCounter = new MessageCounter(42);
        $batchCommitter = new BatchCommitter($committer, $messageCounter, $batchSize);

        for ($i = 0; $i < 7; $i++) {
            $batchCommitter->commitMessage();
        }
    }

    public function testShouldAlwaysCommitDlq()
    {
        $committer = $this->createMock(Committer::class);
        $committer
            ->expects($this->exactly(2))
            ->method('commitDlq');

        $batchSize = 3;
        $messageCounter = new MessageCounter(42);
        $batchCommitter = new BatchCommitter($committer, $messageCounter, $batchSize);

        $batchCommitter->commitDlq();
        $batchCommitter->commitDlq();
    }
}
