<?php

namespace Kafka\Consumer\Tests\Integration\Laravel\Console\Commands;

use Kafka\Consumer\Contracts\Consumer;

class TestConsumer extends Consumer
{
    public static $message;

    public function handle(string $message): void
    {
        self::$message = $message;
        return;
    }
}
