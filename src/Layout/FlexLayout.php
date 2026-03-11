<?php declare(strict_types=1);

namespace PhpTuiDashboard\Layout;

use PhpTuiDashboard\Component;
use PhpTuiDashboard\Area;
use PhpTuiDashboard\Position;
use PhpTuiDashboard\Size;

enum FlexDirection
{
    case ROW;
    case COLUMN;
}

class FlexLayout extends Layout
{
    private FlexDirection $direction;
    private int $spacing;
    private int $padding;

    public function __construct(
        FlexDirection $direction = FlexDirection::ROW,
        int $spacing = 1,
        int $padding = 0,
        Position $position = new Position(0, 0),
        Size $size = new Size(0, 0)
    ) {
        parent::__construct($position, $size);
        $this->direction = $direction;
        $this->spacing = $spacing;
        $this->padding = $padding;
    }

    public function calculateAreas(Area $container): array
    {
        $areas = [];
        $count = count($this->components);
        
        if ($count === 0) {
            return $areas;
        }

        // Adjust container for padding
        $innerArea = new Area(
            new Position(
                $container->position->x + $this->padding,
                $container->position->y + $this->padding
            ),
            new Size(
                $container->size->width - ($this->padding * 2),
                $container->size->height - ($this->padding * 2)
            )
        );

        if ($this->direction === FlexDirection::ROW) {
            $areas = $this->calculateRowLayout($innerArea);
        } else {
            $areas = $this->calculateColumnLayout($innerArea);
        }

        return $areas;
    }

    private function calculateRowLayout(Area $container): array
    {
        $areas = [];
        $sizes = $this->calculateFlexSizes(
            $this->constraints,
            $container->size->width - (count($this->components) - 1) * $this->spacing
        );

        $currentX = $container->position->x;
        foreach ($this->components as $index => $component) {
            $width = $sizes[$index];
            $position = new Position($currentX, $container->position->y);
            $size = new Size($width, $container->size->height);
            $areas[] = new Area($position, $size);
            $currentX += $width + $this->spacing;
        }

        return $areas;
    }

    private function calculateColumnLayout(Area $container): array
    {
        $areas = [];
        $sizes = $this->calculateFlexSizes(
            $this->constraints,
            $container->size->height - (count($this->components) - 1) * $this->spacing
        );

        $currentY = $container->position->y;
        foreach ($this->components as $index => $component) {
            $height = $sizes[$index];
            $position = new Position($container->position->x, $currentY);
            $size = new Size($container->size->width, $height);
            $areas[] = new Area($position, $size);
            $currentY += $height + $this->spacing;
        }

        return $areas;
    }
}
