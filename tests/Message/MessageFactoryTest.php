<?php

namespace Tests\Message;

use Arquivei\Kafka\Dependencies\RdKafka\KafkaMessage;
use Arquivei\Kafka\Message\MessageFactory;
use PHPUnit\Framework\TestCase;

class MessageFactoryTest extends TestCase
{
    public function testCreateShouldReturnNewMessage()
    {
        $kafkaMessage = $this->createMock(KafkaMessage::class);
        $kafkaMessage->expects($this->once())
            ->method('getPayload')
            ->willReturn('{"field":"value"}');

        $factory = new MessageFactory();
        $message = $factory->creteFromKafkaMessage($kafkaMessage);
        $payload = $message->getPayload();

        $expectedPayload = new \stdClass();
        $expectedPayload->field = "value";

        $this->assertEquals($expectedPayload, $payload);
    }
}