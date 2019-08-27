<?php

namespace Tests\Dependencies\RdKafka;

use Arquivei\Kafka\Dependencies\RdKafka\KafkaConsumer;
use Arquivei\Kafka\Dependencies\RdKafka\KafkaMessage;
use Arquivei\Kafka\Message\Message;
use Arquivei\Kafka\Message\MessageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class KafkaConsumerTest extends TestCase
{
    public function testConsumeShouldReturnMessage()
    {
        $readBufferSize = 120000;
        $payload = '{"field":"value"}';

        /** @var MockObject | LibMessage $libMessage */
        $libMessage = $this->createMock(LibMessage::class);
        $libMessage->payload = $payload;
        $libMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $libConsumer = $this->createMock(LibConsumer::class);
        $libConsumer->expects($this->once())
            ->method('consume')
            ->with($readBufferSize)
            ->willReturn($libMessage);

        $message = $this->createMock(Message::class);
        /** @var MockObject | MessageFactory $messageFactory */
        $messageFactory = $this->createMock(MessageFactory::class);
        $messageFactory->expects($this->once())
            ->method('creteFromKafkaMessage')
            ->with(new KafkaMessage($libMessage))
            ->willReturn($message);

        $kafkaConsumer = new KafkaConsumer($messageFactory, $libConsumer, $readBufferSize);
        $consumedMessage = $kafkaConsumer->consume();

        $this->assertEquals($message, $consumedMessage);
    }

    public function testConsumeShouldReturnPartitionEofError()
    {
        $readBufferSize = 120000;
        /** @var MockObject | LibMessage $libMessage */
        $libMessage = $this->createMock(LibMessage::class);;
        $libMessage->err = RD_KAFKA_RESP_ERR__PARTITION_EOF;

        $libConsumer = $this->createMock(LibConsumer::class);
        $libConsumer->expects($this->once())
            ->method('consume')
            ->with($readBufferSize)
            ->willReturn($libMessage);

        /** @var MockObject | MessageFactory $messageFactory */
        $messageFactory = $this->createMock(MessageFactory::class);
        $messageFactory->expects($this->never())
            ->method('creteFromKafkaMessage');

        $kafkaConsumer = new KafkaConsumer($messageFactory, $libConsumer, $readBufferSize);
        $kafkaConsumer->consume();
    }
    public function testConsumeShouldReturnTimedOutError()
    {
        $readBufferSize = 120000;
        /** @var MockObject | LibMessage $libMessage */
        $libMessage = $this->createMock(LibMessage::class);;
        $libMessage->err = RD_KAFKA_RESP_ERR__TIMED_OUT;

        $libConsumer = $this->createMock(LibConsumer::class);
        $libConsumer->expects($this->once())
            ->method('consume')
            ->with($readBufferSize)
            ->willReturn($libMessage);

        /** @var MockObject | MessageFactory $messageFactory */
        $messageFactory = $this->createMock(MessageFactory::class);
        $messageFactory->expects($this->never())
            ->method('creteFromKafkaMessage');

        $kafkaConsumer = new KafkaConsumer($messageFactory, $libConsumer, $readBufferSize);
        $kafkaConsumer->consume();
    }


    /**
     * @expectedException \Arquivei\Kafka\Exceptions\KafkaConsumerException
     * @expectedExceptionMessage An error occurred consuming from kafka: Test Generic Error
     */
    public function testConsumerUnexpectedErrorShouldThrowException()
    {
        $readBufferSize = 120000;
        /** @var MockObject | LibMessage $libMessage */
        $libMessage = $this->createMock(LibMessage::class);
        $libMessage->expects($this->once())
            ->method('errstr')
            ->willReturn('Test Generic Error');

        $libMessage->err = RD_KAFKA_GENERIC_ERROR;

        $libConsumer = $this->createMock(LibConsumer::class);
        $libConsumer->expects($this->once())
            ->method('consume')
            ->with($readBufferSize)
            ->willReturn($libMessage);

        /** @var MockObject | MessageFactory $messageFactory */
        $messageFactory = $this->createMock(MessageFactory::class);
        $messageFactory->expects($this->never())
            ->method('creteFromKafkaMessage');

        $kafkaConsumer = new KafkaConsumer($messageFactory, $libConsumer, $readBufferSize);
        $kafkaConsumer->consume();
    }
}

class LibConsumer
{
    public function consume(){}
}

class LibMessage
{
    public $payload;
    public $err;
    public function errstr() {}
}