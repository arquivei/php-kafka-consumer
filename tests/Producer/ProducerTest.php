<?php

namespace Tests\Producer;

use Arquivei\Kafka\Dependencies\RdKafka\KafkaProducer;
use Arquivei\Kafka\Dependencies\RdKafka\KafkaTopic;
use Arquivei\Kafka\Message\MessageInterface;
use Arquivei\Kafka\Producer\Producer;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class ProducerTest extends TestCase
{
    public function testProduceShouldGetTopicAndProduce()
    {
        $payloadStr = '{"key":"value"}';
        $payload = new \stdClass();
        $payload->key = "value";

        $topic = 'unit.test.arquivei.topic';
        /** @var MockObject | MessageInterface $message */
        $message = $this->createMock(MessageInterface::class);
        $message->expects($this->once())
            ->method('getPayload')
            ->willReturn($payload);

        $kafkaTopic = $this->createMock(KafkaTopic::class);
        $kafkaTopic->expects($this->once())
            ->method('produce')
            ->with($payloadStr);

        /** @var MockObject | KafkaProducer $kafkaProducer */
        $kafkaProducer = $this->createMock(KafkaProducer::class);
        $kafkaProducer->expects($this->once())
            ->method('getTopic')
            ->willReturn($kafkaTopic);

        $producer = new Producer($kafkaProducer);
        $producer->produce($message);
    }
}