<?php declare(strict_types=1);

namespace PhpTuiDashboard;

readonly class Area
{
    public function __construct(
        public Position $position,
        public Size $size
    ) {}

    public function contains(Position $position): bool
    {
        return $position->x >= $this->position->x &&
               $position->x < $this->position->x + $this->size->width &&
               $position->y >= $this->position->y &&
               $position->y < $this->position->y + $this->size->height;
    }

    public function getRight(): int
    {
        return $this->position->x + $this->size->width;
    }

    public function getBottom(): int
    {
        return $this->position->y + $this->size->height;
    }
}
