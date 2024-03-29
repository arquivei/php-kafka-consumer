<?php

declare(strict_types=1);

namespace Kafka\Consumer;

use Closure;
use InvalidArgumentException;
use Kafka\Consumer\Entities\Config;
use Kafka\Consumer\Entities\Config\Sasl;
use Kafka\Consumer\MessageHandler\CallableConsumer;

class ConsumerBuilder
{
    private $topics;
    private $commit;
    private $groupId;
    private $handler;
    private $maxMessages;
    private $maxCommitRetries;
    private $brokers;
    private $middlewares;
    private $saslConfig = null;
    private $dlq;
    private $securityProtocol;
    private $autoCommit;
    private $options;
    private $printConfigs;

    private function __construct(string $brokers, string $groupId, array $topics)
    {
        foreach ($topics as $topic) {
            if (!is_string($topic)) {
                throw new InvalidArgumentException('The topic name should be a string value');
            }
        }

        $this->brokers = $brokers;
        $this->groupId = $groupId;
        $this->topics = $topics;

        $this->commit = 1;
        $this->handler = function () {
        };
        $this->maxMessages = -1;
        $this->maxCommitRetries = 6;
        $this->middlewares = [];
        $this->securityProtocol = 'PLAINTEXT';
        $this->autoCommit = false;
        $this->options = [];
        $this->printConfigs = false;
    }

    public static function create(string $brokers, $groupId, array $topics): self
    {
        return new ConsumerBuilder($brokers, $groupId, $topics);
    }

    public function withCommitBatchSize(int $size): self
    {
        $this->commit = $size;
        return $this;
    }

    /**
     * The function that will handle the incoming messages
     *
     * @param callable(mixed $message): void $handler
     */
    public function withHandler(callable $handler): self
    {
        $this->handler = Closure::fromCallable($handler);
        return $this;
    }

    public function withMaxMessages(int $maxMessages): self
    {
        $this->maxMessages = $maxMessages;
        return $this;
    }

    public function withMaxCommitRetries(int $maxCommitRetries): self
    {
        $this->maxCommitRetries = $maxCommitRetries;
        return $this;
    }

    public function withDlq(?string $dlqTopic = null): self
    {
        if (null === $dlqTopic) {
            $dlqTopic = $this->topics[0] . '-dlq';
        }

        $this->dlq = $dlqTopic;

        return $this;
    }

    public function withSasl(Sasl $saslConfig): self
    {
        $this->saslConfig = $saslConfig;
        return $this;
    }

    /**
     * The middlewares get executed in the order they are defined.
     *
     * The middleware is a callable in which the first argument is the message itself and the second is the next handler
     *
     * @param callable(mixed, callable): void $middleware
     * @return $this
     */
    public function withMiddleware(callable $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function withSecurityProtocol(string $securityProtocol): self
    {
        $this->securityProtocol = $securityProtocol;
        return $this;
    }

    public function withAutoCommit(): self
    {
        $this->autoCommit = true;
        return $this;
    }

    public function withOptions(array $options): self
    {
        foreach ($options as $name => $value) {
            $this->withOption($name, $value);
        }

        return $this;
    }

    public function withOption(string $name, string $value): self
    {
        $this->options[$name] = $value;
        return $this;
    }

    public function withPrintConfigs(bool $printConfigs): self
    {
        $this->printConfigs = $printConfigs;
        return $this;
    }

    public function build(): Consumer
    {
        $config = new Config(
            $this->saslConfig,
            $this->topics,
            $this->brokers,
            $this->commit,
            $this->groupId,
            new CallableConsumer($this->handler, $this->middlewares),
            $this->securityProtocol,
            $this->dlq,
            $this->maxMessages,
            $this->maxCommitRetries,
            $this->autoCommit,
            $this->options,
            $this->printConfigs
        );

        return new Consumer(
            $config
        );
    }
}
