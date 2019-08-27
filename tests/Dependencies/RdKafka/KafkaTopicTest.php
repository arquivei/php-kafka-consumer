<?php

namespace Tests\Dependencies\RdKafka;

use Arquivei\Kafka\Dependencies\RdKafka\KafkaTopic;
use Tests\TestCase;

class KafkaTopicTest extends TestCase
{
    public function testProduceShouldCallLibProduce()
    {
        $partition = 1;
        $producerKey = 'blah';
        $payload = '{"key":"value"}';
        $libTopic = $this->createMock(LibTopic::class);
        $libTopic->expects($this->once())
            ->method('produce')
            ->with($partition, 0, $payload, $producerKey);

        $kafkaTopic = new KafkaTopic($libTopic);
        $kafkaTopic->produce($payload, $partition, $producerKey);
    }


    public function testProduceDefaultsShouldCallLibProduce()
    {
        $payload = '{"key":"value"}';
        $libTopic = $this->createMock(LibTopic::class);
        $libTopic->expects($this->once())
            ->method('produce')
            ->with(RD_KAFKA_PARTITION_UA, 0, $payload, null);

        $kafkaTopic = new KafkaTopic($libTopic);
        $kafkaTopic->produce($payload);
    }
}

class LibTopic
{
    public function produce($partition, $flags, $payload, $producerKey = null) {}
}