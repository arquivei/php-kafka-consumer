<?php

namespace Kafka\Consumer\Tests\Integration;

use Kafka\Consumer\ConsumerBuilder;
use Kafka\Consumer\Events\EventDispatcher;
use Kafka\Consumer\Events\MessageHandled;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    public function testSuccess(): void
    {
        $topicName = 'php-kafka-consumer-topic';
        $this->sendMessage($topicName, 'Groundhog day');

        $this->consumeMessages(1, $topicName, new TestConsumer());

        $this->assertSame(1, count(TestConsumer::$messages));
        $this->assertContains('Groundhog day', TestConsumer::$messages);
    }

    public function testDlq(): void
    {
        $topicName = 'php-kafka-consumer-topic';
        $this->sendMessage($topicName, 'Cest la vie');

        $this->consumeMessages(
            1,
            $topicName,
            new TestConsumer([TestConsumer::RESPONSE_ERROR, TestConsumer::RESPONSE_ERROR])
        );

        $this->assertEmpty(TestConsumer::$messages);

        $this->consumeMessages(1, $topicName . '-dlq', new TestConsumer());

        $this->assertSame(1, count(TestConsumer::$messages));
        $this->assertContains('Cest la vie', TestConsumer::$messages);
    }

    public function testMultipleMessages(): void
    {
        $topicName = 'php-kafka-consumer-topic';
        $this->sendMessage($topicName, 'You are my fire');
        $this->sendMessage($topicName, 'The one desire');
        $this->sendMessage($topicName, 'Believe when I say');
        $this->sendMessage($topicName, 'I want it that way');

        $this->consumeMessages(4, $topicName, new TestConsumer());

        $this->assertSame(4, count(TestConsumer::$messages));
        $this->assertContains('You are my fire', TestConsumer::$messages);
        $this->assertContains('The one desire', TestConsumer::$messages);
        $this->assertContains('Believe when I say', TestConsumer::$messages);
        $this->assertContains('I want it that way', TestConsumer::$messages);
    }

    public function testStopExecution(): void
    {
        $topicName = 'php-kafka-consumer-topic';
        $this->sendMessage($topicName, 'You are my fire');
        $this->sendMessage($topicName, 'The one desire');
        $this->sendMessage($topicName, 'Stop execution');
        $this->sendMessage($topicName, 'Believe when I say');
        $this->sendMessage($topicName, 'I want it that way');

        $this->consumeMessages(99, $topicName, new TestConsumer(), [
            MessageHandled::class => function (MessageHandled $event) {
                if ($event->getRawMessage() === 'Stop execution') {
                    $event->stopExecution();
                }
            }
        ]);

        $this->assertSame(3, count(TestConsumer::$messages));
        $this->assertContains('You are my fire', TestConsumer::$messages);
        $this->assertContains('The one desire', TestConsumer::$messages);
        $this->assertContains('Stop execution', TestConsumer::$messages);

        $this->consumeMessages(2, $topicName, new TestConsumer());
    }

    private function sendMessage(string $topicName, string $msg)
    {
        $rdKafkaConf = new \RdKafka\Conf();
        $rdKafkaConf->set('log_level', (string) LOG_DEBUG);
        $rdKafkaConf->set('debug', 'all');
        $rdKafkaConf->set('security.protocol', 'PLAINTEXT');
        $rdKafkaConf->set('sasl.mechanisms', 'PLAIN');
        $rdKafkaConf->set('bootstrap.servers', env('KAFKA_BROKERS'));

        $producer = new \RdKafka\Producer($rdKafkaConf);
        $producer->addBrokers(env('KAFKA_BROKERS'));

        $topic = $producer->newTopic($topicName);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $msg);

        if (method_exists($producer, 'flush')) {
            $producer->flush(12000);
        }
    }

    private function consumeMessages(
        int $numberOfMessages,
        string $topicName,
        callable $handler,
        array $subscribers = []
    ): void {
        $builder = ConsumerBuilder::create(env('KAFKA_BROKERS'), 'test-group-id', [$topicName])
            ->withCommitBatchSize(1)
            ->withMaxCommitRetries(6)
            ->withHandler($handler)
            ->withDlq($topicName . '-dlq')
            ->withMaxMessages($numberOfMessages);

        foreach ($subscribers as $event => $subscriber) {
            $builder->withSubscriber($event, $subscriber);
        }

        $consumer = $builder->build();

        $consumer->consume();
    }
}
