<?php

namespace Tests\Consumer\Error;

use Arquivei\Kafka\Consumer\Error\DlqErrorHandler;
use Arquivei\Kafka\Message\MessageInterface;
use Arquivei\Kafka\Producer\ProducerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class DlqErrorHandlerTest extends TestCase
{
    public function testHandlerShouldEnqueueToDlq()
    {
        /** @var MockObject | MessageInterface $message */
        $message= $this->createMock(MessageInterface::class);

        /** @var MockObject | \Throwable $exception */
        $exception = $this->createMock(\Throwable::class);

        /** @var MockObject | ProducerInterface $dlqProducer */
        $dlqProducer = $this->createMock(ProducerInterface::class);
        $dlqProducer->expects($this->once())
            ->method('produce')
            ->with($message);

        $errorHandler = new DlqErrorHandler($dlqProducer);
        $errorHandler->handle($message, $exception);
    }
}