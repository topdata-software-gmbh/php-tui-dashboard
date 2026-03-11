<?php declare(strict_types=1);

namespace PhpTuiDashboard;

readonly class Position
{
    public function __construct(
        public int $x,
        public int $y
    ) {}

    public function translate(int $dx, int $dy): Position
    {
        return new Position($this->x + $dx, $this->y + $dy);
    }

    public function __toString(): string
    {
        return "{$this->x},{$this->y}";
    }
}
