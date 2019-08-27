<?php

namespace Tests\Consumer;

use Arquivei\Kafka\Consumer\Consumer;
use Arquivei\Kafka\Consumer\MessageExecutorInterface;
use Arquivei\Kafka\Dependencies\RdKafka\KafkaConsumer;
use Arquivei\Kafka\Message\Message;
use Arquivei\Kafka\Consumer\Error\ErrorHandlerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConsumerTest extends TestCase
{
    public function testConsumerShouldConsumeSuccessfully()
    {
        $message = $this->createMock(Message::class);
        /** @var MockObject | KafkaConsumer $kafkaConsumer */
        $kafkaConsumer = $this->createMock(KafkaConsumer::class);
        $kafkaConsumer->expects($this->once())
            ->method('consume')
            ->willReturn($message);

        /** @var MockObject | MessageExecutorInterface $messageExecutor */
        $messageExecutor = $this->createMock(MessageExecutorInterface::class);
        $messageExecutor->expects($this->once())
            ->method('execute')
            ->with($message)
            ->willReturn(true);

        /** @var MockObject | ErrorHandlerInterface $errorHandler */
        $errorHandler = $this->createMock(ErrorHandlerInterface::class);
        $errorHandler->expects($this->never())
            ->method('handle');

        $consumer = new Consumer(
            $kafkaConsumer,
            $messageExecutor,
            $errorHandler
        );

        $consumedMessage = $consumer->consume();
        $this->assertEquals($message, $consumedMessage);
    }

    public function testConsumerShouldIgnoreNullMessage()
    {
        /** @var MockObject | KafkaConsumer $kafkaConsumer */
        $kafkaConsumer = $this->createMock(KafkaConsumer::class);
        $kafkaConsumer->expects($this->once())
            ->method('consume')
            ->willReturn(null);

        /** @var MockObject | MessageExecutorInterface $messageExecutor */
        $messageExecutor = $this->createMock(MessageExecutorInterface::class);
        $messageExecutor->expects($this->never())
            ->method('execute');

        /** @var MockObject | ErrorHandlerInterface $errorHandler */
        $errorHandler = $this->createMock(ErrorHandlerInterface::class);
        $errorHandler->expects($this->never())
            ->method('handle');

        $consumer = new Consumer(
            $kafkaConsumer,
            $messageExecutor,
            $errorHandler
        );

        $consumedMessage = $consumer->consume();
        $this->assertNull($consumedMessage);
    }

    public function testConsumerShouldHandleException()
    {
        $message = $this->createMock(Message::class);
        /** @var MockObject | KafkaConsumer $kafkaConsumer */
        $kafkaConsumer = $this->createMock(KafkaConsumer::class);
        $kafkaConsumer->expects($this->once())
            ->method('consume')
            ->willReturn($message);

        /** @var MockObject | MessageExecutorInterface $messageExecutor */
        $messageExecutor = $this->createMock(MessageExecutorInterface::class);
        $executionException = new \Exception('Executor Exception');
        $messageExecutor->expects($this->once())
            ->method('execute')
            ->with($message)
            ->willThrowException($executionException);

        /** @var MockObject | ErrorHandlerInterface $errorHandler */
        $errorHandler = $this->createMock(ErrorHandlerInterface::class);
        $errorHandler->expects($this->once())
            ->method('handle')
            ->with($message, $executionException);

        $consumer = new Consumer(
            $kafkaConsumer,
            $messageExecutor,
            $errorHandler
        );

        $consumedMessage = $consumer->consume();
        $this->assertEquals($message, $consumedMessage);
    }
}
