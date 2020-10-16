<?php

namespace Kafka\Consumer\Tests\Integration;

use Kafka\Consumer\Contracts\Consumer;

class TestConsumer extends Consumer
{
    public const RESPONSE_OK = 'response_ok';
    public const RESPONSE_ERROR = 'response_error';

    public static $messages;
    public static $responses;
    private static $callCounter;

    public function __construct(array $responses = [])
    {
        self::$messages = [];
        self::$responses = $responses;
        self::$callCounter = 0;
    }

    public function handle(string $message): void
    {
        if (!empty(self::$responses)) {
            $responseDirective = self::$responses[self::$callCounter++];
            if ($responseDirective == self::RESPONSE_ERROR) {
                throw new \Exception('Error processing message');
            } elseif (is_a(\Throwable::class, $responseDirective)) {
                throw $responseDirective;
            }
        }

        self::$messages[] = $message;

        return;
    }
}
