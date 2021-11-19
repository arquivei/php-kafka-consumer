<?php

declare(strict_types=1);

namespace Kafka\Consumer\Commit;

use Kafka\Consumer\Retry\Retryable;

/**
 * Decorates a committer with retry logic
 *
 * It implements the exponential backoff algorithm
 */
class RetryableCommitter implements Committer
{
    private const RETRYABLE_ERRORS = [
        RD_KAFKA_RESP_ERR_REQUEST_TIMED_OUT
    ];

    private $committer;
    private $retryable;

    public function __construct(Committer $committer, Sleeper $sleeper, int $maximumRetries = 6)
    {
        $this->committer = $committer;
        $this->retryable = new Retryable($sleeper, $maximumRetries, self::RETRYABLE_ERRORS);
    }

    public function commitMessage(): void
    {
        $this->retryable->retry(function () {
            $this->committer->commitMessage();
        });
    }

    public function commitDlq(): void
    {
        $this->retryable->retry(function () {
            $this->committer->commitDlq();
        });
    }
}
