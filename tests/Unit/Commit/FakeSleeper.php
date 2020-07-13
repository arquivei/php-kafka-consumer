<?php

declare(strict_types=1);

namespace Kafka\Consumer\Tests\Unit\Commit;

use Kafka\Consumer\Commit\Sleeper;

class FakeSleeper implements Sleeper
{
    private $sleeps = [];

    public function sleep(int $timeInMicroseconds): void
    {
        $this->sleeps[] = $timeInMicroseconds;
    }

    public function getSleeps(): array
    {
        return $this->sleeps;
    }
}
