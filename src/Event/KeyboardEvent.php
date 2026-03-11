<?php declare(strict_types=1);

namespace PhpTuiDashboard\Event;

class KeyboardEvent implements Event
{
    private string $type;
    private array $data;
    private float $timestamp;

    public function __construct(string $key, array $modifiers = [])
    {
        $this->type = 'keyboard.' . $key;
        $this->data = [
            'key' => $key,
            'modifiers' => $modifiers
        ];
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

    public function getKey(): string
    {
        return $this->data['key'];
    }

    public function hasModifier(string $modifier): bool
    {
        return in_array($modifier, $this->data['modifiers']);
    }
}
