<?php

namespace Tests\Dependencies\RdKafka;

use Arquivei\Kafka\Dependencies\RdKafka\KafkaProducer;
use Arquivei\Kafka\Dependencies\RdKafka\KafkaTopic;
use Tests\TestCase;

class KafkaProducerTest extends TestCase
{
    public function testGetTopicShouldBeExecutedSuccessfully()
    {
        $topicName = 'unit.tests.arquivei.dlq';
        $libTopic = $this->createMock(LibTopic::class);

        $libProducer = $this->createMock(LibProducer::class);
        $libProducer->expects($this->once())
            ->method('newTopic')
            ->with($topicName)
            ->willReturn($libTopic);

        $kafkaProducer = new KafkaProducer($libProducer, $topicName);
        $topic = $kafkaProducer->getTopic();

        $this->assertInstanceOf(KafkaTopic::class, $topic);
    }

    public function testMultiplesGetTopicShouldReturnSameInstance()
    {
        $topicName = 'unit.tests.arquivei.dlq';
        $libTopic = $this->createMock(LibTopic::class);

        $libProducer = $this->createMock(LibProducer::class);
        $libProducer->expects($this->atLeastOnce())
            ->method('newTopic')
            ->with($topicName)
            ->willReturn($libTopic);

        $kafkaProducer = new KafkaProducer($libProducer, $topicName);

        $this->assertSame(
            $kafkaProducer->getTopic(),
            $kafkaProducer->getTopic()
        );
    }
}

class LibProducer
{
    public function newTopic(string $topicName) {}
}

