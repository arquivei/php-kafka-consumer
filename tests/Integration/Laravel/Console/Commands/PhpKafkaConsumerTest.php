<?php

namespace Kafka\Consumer\Tests\Integration\Laravel\Console\Commands;

use Kafka\Consumer\Exceptions\InvalidCommitException;
use Kafka\Consumer\Exceptions\InvalidConsumerException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;

class PhpKafkaConsumerTest extends TestCase
{
    private $status;
    private $input;
    private $kernel;

    protected function setUp(): void
    {
        $app = new \Illuminate\Foundation\Application(
            '/application/laravel-test'
        );

        $app->singleton(
            \Illuminate\Contracts\Http\Kernel::class,
            \App\Http\Kernel::class
        );

        $app->singleton(
            \Illuminate\Contracts\Console\Kernel::class,
            \App\Console\Kernel::class
        );

        $app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \App\Exceptions\Handler::class
        );

        $this->kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $this->input = new ArgvInput();
    }

    public function testSuccess()
    {
        $rdKafkaConf = new \RdKafka\Conf();
        $rdKafkaConf->set('log_level', (string) LOG_DEBUG);
        $rdKafkaConf->set('debug', 'all');
        $rdKafkaConf->set('security.protocol', 'PLAINTEXT');
        $rdKafkaConf->set('sasl.mechanisms', 'PLAIN');

        $producer = new \RdKafka\Producer($rdKafkaConf);
        $producer->addBrokers(env('KAFKA_BROKERS'));

        $topic = $producer->newTopic('php-kafka-consumer-topic');
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, 'What a lovely day!');

        $this->status = $this->kernel->call('arquivei:php-kafka-consumer', ['--topic' => 'php-kafka-consumer-topic', '--consumer' => TestConsumer::class, '--groupId' => 'test-group-id', '--commit' => '1', '--dlq' => 'php-kafka-consumer-topic-dlq', '--maxMessage' => 1,]);
        $msg = TestConsumer::$message;
        $this->assertSame($msg, 'What a lovely day!');
    }

    public function testInvalidCommit()
    {
        $this->expectException(InvalidCommitException::class);
        $this->kernel->call('arquivei:php-kafka-consumer', ['--topic' => 'php-kafka-consumer-topic', '--consumer' => TestConsumer::class, '--groupId' => 'test-group-id', '--commit' => '-20', '--dlq' => 'php-kafka-consumer-topic-dlq', '--maxMessage' => 1,]);
    }

    public function testInvalidConsumer()
    {
        $this->expectException(InvalidConsumerException::class);
        $this->kernel->call('arquivei:php-kafka-consumer', ['--topic' => 'php-kafka-consumer-topic', '--consumer' => 'not-a-consuer', '--groupId' => 'test-group-id', '--commit' => '1', '--dlq' => 'php-kafka-consumer-topic-dlq', '--maxMessage' => 1,]);
    }

    public function tearDown(): void
    {
        $this->kernel->terminate($this->input, $this->status);
    }
}
