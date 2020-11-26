<?php

declare(strict_types=1);

namespace Kafka\Consumer\Tests\Unit\Commit\MessageHandler;

use Kafka\Consumer\MessageHandler\CallableConsumer;
use Monolog\Test\TestCase;
use stdClass;

class CallableConsumerTest extends TestCase
{
    public function testShouldDecodeMessages(): void
    {
        $rawMessage = <<<JSON
{
    "foo": "bar"
}
JSON;

        $consumer = new CallableConsumer([$this, 'handleMessage'], [
            function (string $message, callable $next): void {
                $decoded = json_decode($message);
                $next($decoded);
            },
            function (stdClass $message, callable $next): void {
                $decoded = (array) $message;
                $next($decoded);
            }
        ]);

        $consumer->handle($rawMessage);
    }

    public function handleMessage(array $data): void
    {
        $this->assertEquals([
            'foo' => 'bar'
        ], $data);
    }
}
