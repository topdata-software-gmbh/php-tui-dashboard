<?php declare(strict_types=1);

namespace PhpTuiDashboard;

readonly class Size
{
    public function __construct(
        public int $width,
        public int $height
    ) {}

    public function isEmpty(): bool
    {
        return $this->width <= 0 || $this->height <= 0;
    }

    public function __toString(): string
    {
        return "{$this->width}x{$this->height}";
    }
}
