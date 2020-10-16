<?php

namespace Kafka\Consumer\Tests\Integration;

use Kafka\Consumer\Tests\Integration\TestConsumer;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    public function testSuccess()
    {
        $topicName = 'php-kafka-consumer-topic-success-test';
        $this->sendMessage($topicName, 'Groundhog day');

        $config = new \Kafka\Consumer\Entities\Config(
            new \Kafka\Consumer\Entities\Config\Sasl(
                '',
                '',
                'PLAIN'
            ),
            [$topicName],
            env('KAFKA_BROKERS'),
            1,
            'test-group-id',
            new TestConsumer(),
            'PLAINTEXT',
            $topicName . '-dlq',
            1,
            6
        );

        (new \Kafka\Consumer\Consumer($config))->consume();

        $this->assertSame(1, count(TestConsumer::$messages));
        $this->assertContains('Groundhog day', TestConsumer::$messages);
    }

    public function testDlq()
    {
        $topicName = 'php-kafka-consumer-topic-dlq-test';
        $this->sendMessage($topicName, 'Cest la vie');

        $config = new \Kafka\Consumer\Entities\Config(
            new \Kafka\Consumer\Entities\Config\Sasl(
                '',
                '',
                'PLAIN'
            ),
            [$topicName],
            env('KAFKA_BROKERS'),
            1,
            'test-group-id',
            new TestConsumer([TestConsumer::RESPONSE_ERROR, TestConsumer::RESPONSE_ERROR]),
            'PLAINTEXT',
            $topicName . '-dlq',
            1,
            6
        );

        (new \Kafka\Consumer\Consumer($config))->consume();

        $this->assertEmpty(TestConsumer::$messages);
    }

    public function testMultipleMessages()
    {
        $topicName = 'php-kafka-consumer-topic-multi-test';
        $this->sendMessage($topicName, 'You are my fire');
        $this->sendMessage($topicName, 'The one desire');
        $this->sendMessage($topicName, 'Believe when I say');
        $this->sendMessage($topicName, 'I want it that way');

        $config = new \Kafka\Consumer\Entities\Config(
            new \Kafka\Consumer\Entities\Config\Sasl(
                '',
                '',
                'PLAIN'
            ),
            [$topicName],
            env('KAFKA_BROKERS'),
            1,
            'test-group-id',
            new TestConsumer(),
            'PLAINTEXT',
            $topicName . '-dlq',
            4,
            6
        );

        (new \Kafka\Consumer\Consumer($config))->consume();

        $this->assertSame(4, count(TestConsumer::$messages));
        $this->assertContains('You are my fire', TestConsumer::$messages);
        $this->assertContains('The one desire', TestConsumer::$messages);
        $this->assertContains('Believe when I say', TestConsumer::$messages);
        $this->assertContains('I want it that way', TestConsumer::$messages);
    }

    private function sendMessage(string $topicName, string $msg)
    {
        $rdKafkaConf = new \RdKafka\Conf();
        $rdKafkaConf->set('log_level', (string) LOG_DEBUG);
        $rdKafkaConf->set('debug', 'all');
        $rdKafkaConf->set('security.protocol', 'PLAINTEXT');
        $rdKafkaConf->set('sasl.mechanisms', 'PLAIN');

        $producer = new \RdKafka\Producer($rdKafkaConf);
        $producer->addBrokers(env('KAFKA_BROKERS'));

        $topic = $producer->newTopic($topicName);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $msg);
    }
}
