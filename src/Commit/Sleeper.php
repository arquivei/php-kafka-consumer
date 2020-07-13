<?php

declare(strict_types=1);

namespace Kafka\Consumer\Commit;

interface Sleeper
{
    public function sleep(int $timeInMicroseconds): void;
}
