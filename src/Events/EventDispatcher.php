<?php

declare(strict_types=1);

namespace Kafka\Consumer\Events;

class EventDispatcher
{
    private $subscribers = [];

    public function addSubscriber(string $eventName, callable $subscriber): self
    {
        $this->subscribers[$eventName][] = $subscriber;
        return $this;
    }

    public function dispatch(object $event): void
    {
        $subscribers = $this->subscribers[get_class($event)] ?? [];

        foreach ($subscribers as $subscriber) {
            $subscriber($event);
        }
    }
}
