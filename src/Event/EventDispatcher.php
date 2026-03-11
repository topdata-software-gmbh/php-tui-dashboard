<?php declare(strict_types=1);

namespace PhpTuiDashboard\Event;

class EventDispatcher
{
    private array $listeners = [];
    private array $eventQueue = [];

    public function addListener(string $eventType, callable $listener): void
    {
        $this->listeners[$eventType][] = $listener;
    }

    public function removeListener(string $eventType, callable $listener): void
    {
        if (!isset($this->listeners[$eventType])) {
            return;
        }
        
        $this->listeners[$eventType] = array_filter(
            $this->listeners[$eventType],
            fn($l) => $l !== $listener
        );
    }

    public function dispatch(Event $event): void
    {
        $this->eventQueue[] = $event;
    }

    public function dispatchEvents(): void
    {
        while (!empty($this->eventQueue)) {
            $event = array_shift($this->eventQueue);
            $this->processEvent($event);
        }
    }

    private function processEvent(Event $event): void
    {
        $eventType = $event->getType();
        
        // Call specific listeners
        if (isset($this->listeners[$eventType])) {
            foreach ($this->listeners[$eventType] as $listener) {
                $listener($event);
            }
        }
        
        // Call wildcard listeners
        $wildcardType = substr($eventType, 0, strrpos($eventType, '.'));
        if (isset($this->listeners[$wildcardType . '.*'])) {
            foreach ($this->listeners[$wildcardType . '.*'] as $listener) {
                $listener($event);
            }
        }
    }
}
