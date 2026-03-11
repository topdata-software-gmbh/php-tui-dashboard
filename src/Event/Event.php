<?php declare(strict_types=1);

namespace PhpTuiDashboard\Event;

interface Event
{
    public function getType(): string;
    public function getData(): array;
    public function getTimestamp(): float;
}
