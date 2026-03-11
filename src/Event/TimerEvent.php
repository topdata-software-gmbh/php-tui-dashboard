<?php declare(strict_types=1);

namespace PhpTuiDashboard\Event;

class TimerEvent implements Event
{
    private string $type;
    private array $data;
    private float $timestamp;

    public function __construct(string $timerId, array $data = [])
    {
        $this->type = 'timer.' . $timerId;
        $this->data = array_merge(['timerId' => $timerId], $data);
        $this->timestamp = microtime(true);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getTimestamp(): float
    {
        return $this->timestamp;
    }

    public function getTimerId(): string
    {
        return $this->data['timerId'];
    }
}
