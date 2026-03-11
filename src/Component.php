<?php declare(strict_types=1);

namespace PhpTuiDashboard;

abstract class Component
{
    protected Position $position;
    protected Size $size;
    protected bool $visible = true;
    protected array $styles = [];

    public function __construct(Position $position, Size $size)
    {
        $this->position = $position;
        $this->size = $size;
    }

    public function getPosition(): Position
    {
        return $this->position;
    }

    public function setPosition(Position $position): void
    {
        $this->position = $position;
    }

    public function getSize(): Size
    {
        return $this->size;
    }

    public function setSize(Size $size): void
    {
        $this->size = $size;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    abstract public function render(Renderer $renderer): void;

    public function getArea(): Area
    {
        return new Area($this->position, $this->size);
    }
}
