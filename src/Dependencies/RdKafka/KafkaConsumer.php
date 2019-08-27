<?php

namespace Arquivei\Kafka\Dependencies\RdKafka;

use Arquivei\Kafka\Exceptions\KafkaConsumerException;
use Arquivei\Kafka\Message\MessageFactory;
use Arquivei\Kafka\Message\MessageInterface;

class KafkaConsumer
{
    private $messageFactory;
    private $libConsumer;
    private $readBufferSize;

    public function __construct(MessageFactory $messageFactory, $libConsumer, int $readBufferSize)
    {
        $this->messageFactory = $messageFactory;
        $this->libConsumer = $libConsumer;
        $this->readBufferSize = $readBufferSize;
    }

    /**
     * Gets a message from Kafka and Convert to a MessageInterface Objects
     * This method ignores PARTITION_EOF and TIMED_OUT errors returning null. Any other error will throw an exception
     *
     * @return MessageInterface|null
     * @throws KafkaConsumerException
     */
    public function consume() : ?MessageInterface
    {
        $kafkaMessage = new KafkaMessage(
            $this->libConsumer->consume($this->readBufferSize)
        );

        if ($kafkaMessage->getError() == KafkaMessage::NO_ERROR){
            return $this->messageFactory->creteFromKafkaMessage($kafkaMessage);
        }

        if ($kafkaMessage->getError() == KafkaMessage::ERROR_PARTITION_EOF or
            $kafkaMessage->getError() == KafkaMessage::ERROR_TIMED_OUT
        ) {
            return null;
        }

        $exceptionMessage = sprintf(
            "An error occurred consuming from kafka: %s",
            $kafkaMessage->getErrorMessage()
        );
        throw new KafkaConsumerException($exceptionMessage);
    }

    public function commit() : void
    {
        $this->libConsumer->commit();
    }
}
