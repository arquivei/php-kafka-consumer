<?php

namespace Kafka\Consumer\Log;

use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;
use Monolog\Formatter\JsonFormatter;

class Logger
{
    private $logger;

    public function __construct()
    {
        $handler = new StreamHandler("php://stdout");
        $handler->setFormatter(new JsonFormatter())
            ->pushProcessor(new UidProcessor(32));
        $this->logger = new \Monolog\Logger('PHP-KAFKA-CONSUMER-ERROR');
        $this->logger->pushHandler($handler);
        $this->logger->pushProcessor(function ($record) {
            if (is_array($record)) {
                $record['datetime'] = $record['datetime']->format('c');
            }

            return $record;
        });
    }

    public function error(\RdKafka\Message $message, \Throwable $exception = null, string $prefix = 'ERROR'): void
    {
        $this->logger->error("[$prefix] Error to consume message", [
            'message' => $message,
            'throwable' => $exception
        ]);
    }
}
