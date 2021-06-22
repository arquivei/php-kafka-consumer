<?php

declare(strict_types=1);

namespace Kafka\Consumer\Tests\Unit;

use Kafka\Consumer\Contracts\Consumer;
use Kafka\Consumer\Entities\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testShouldReturnDefaultKafkaOptions(): void
    {
        $config = new Config(
            null,
            ['topic'],
            'broker',
            1,
            'group',
            $this->createMock(Consumer::class),
            'security',
            null
        );

        $expectedOptions = [
            'auto.offset.reset' => 'smallest',
            'queued.max.messages.kbytes' => '10000',
            'enable.auto.commit' => 'false',
            'compression.codec' => 'gzip',
            'max.poll.interval.ms' => '86400000',
            'group.id' => 'group',
            'bootstrap.servers' => 'broker',
            'security.protocol' => 'security',
        ];

        $this->assertEquals(
            $expectedOptions,
            $config->getConsumerOptions()
        );
    }

    public function testShouldAddAuthentication(): void
    {
        $sasl = new Config\Sasl('foo', 'bar', 'mec');

        $config = new Config(
            $sasl,
            ['topic'],
            'broker',
            1,
            'group',
            $this->createMock(Consumer::class),
            'SASL_PLAINTEXT',
            null
        );

        $kafkaOptions = $config->getConsumerOptions();

        $this->assertEquals(
            'foo',
            $kafkaOptions['sasl.username']
        );

        $this->assertEquals(
            'bar',
           $kafkaOptions['sasl.password']
        );

        $this->assertEquals(
            'mec',
           $kafkaOptions['sasl.mechanisms']
        );
    }

    public function testShouldOverrideOptions(): void
    {
        $config = new Config(
            null,
            ['topic'],
            'broker',
            1,
            'group',
            $this->createMock(Consumer::class),
            'security',
            null,
            -1,
            6,
            true,
            ['auto.offset.reset' => 'latest']
        );

        $expectedOptions = [
            'auto.offset.reset' => 'latest',
            'queued.max.messages.kbytes' => '10000',
            'enable.auto.commit' => 'true',
            'compression.codec' => 'gzip',
            'max.poll.interval.ms' => '86400000',
            'group.id' => 'group',
            'bootstrap.servers' => 'broker',
            'security.protocol' => 'security',
        ];

        $this->assertEquals(
            $expectedOptions,
            $config->getConsumerOptions()
        );
    }

    public function testShouldReturnProducerOptions(): void
    {
        $sasl = new Config\Sasl(
            'user',
            'pass',
            'mec'
        );

        $config = new Config(
            $sasl,
            ['topic'],
            'broker',
            1,
            'group',
            $this->createMock(Consumer::class),
            'SASL_PLAINTEXT',
            null
        );

        $expectedOptions = [
            'compression.codec' => 'gzip',
            'bootstrap.servers' => 'broker',
            'security.protocol' => 'SASL_PLAINTEXT',
            'sasl.username' => 'user',
            'sasl.password' => 'pass',
            'sasl.mechanisms' => 'mec'
        ];

        $this->assertEquals(
            $expectedOptions,
            $config->getProducerOptions()
        );
    }
}
