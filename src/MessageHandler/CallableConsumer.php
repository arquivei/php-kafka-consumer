<?php

declare(strict_types=1);

namespace Kafka\Consumer\MessageHandler;

use Closure;
use Kafka\Consumer\Contracts\Consumer;

class CallableConsumer extends Consumer
{
    private $handler;
    private $middlewares;

    public function __construct(callable $handler, array $middlewares)
    {
        $this->handler = Closure::fromCallable($handler);
        $this->middlewares = array_map([$this, 'wrapMiddleware'], $middlewares);
        $this->middlewares[] = $this->wrapMiddleware(function ($message, callable $next) {
            $next($message);
        });
    }

    public function handle(string $message): void
    {
        $middlewares = array_reverse($this->middlewares);
        $handler = array_shift($middlewares)($this->handler);

        foreach ($middlewares as $middleware) {
            $handler = $middleware($handler);
        }

        $handler($message);
    }

    private function wrapMiddleware(callable $middleware): callable
    {
        return function (callable $handler) use ($middleware) {
            return function ($message) use ($handler, $middleware) {
                $middleware($message, $handler);
            };
        };
    }
}
