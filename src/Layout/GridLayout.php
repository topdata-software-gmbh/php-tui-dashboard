<?php declare(strict_types=1);

namespace PhpTuiDashboard\Layout;

use PhpTuiDashboard\Component;
use PhpTuiDashboard\Area;
use PhpTuiDashboard\Position;
use PhpTuiDashboard\Size;

class GridItem
{
    public function __construct(
        public Component $component,
        public int $column,
        public int $row,
        public int $columnSpan = 1,
        public int $rowSpan = 1
    ) {}
}

class GridLayout extends Layout
{
    private int $columns;
    private int $rows;
    private int $spacing;
    private array $gridItems = [];

    public function __construct(int $columns = 2, int $rows = 2, int $spacing = 1, Position $position = new Position(0, 0), Size $size = new Size(0, 0))
    {
        parent::__construct($position, $size);
        $this->columns = $columns;
        $this->rows = $rows;
        $this->spacing = $spacing;
    }

    public function addComponent(Component $component, mixed $constraint = null): void
    {
        if ($constraint instanceof GridItem) {
            $this->gridItems[] = $constraint;
        } else {
            // Auto-place in next available position
            $position = $this->findNextAvailablePosition();
            $this->gridItems[] = new GridItem($component, $position['x'], $position['y']);
        }
        
        $this->components[] = $component;
        $this->constraints[] = $constraint;
    }

    public function calculateAreas(Area $container): array
    {
        $areas = [];
        
        // Calculate cell sizes
        $cellWidth = ($container->size->width - ($this->columns - 1) * $this->spacing) / $this->columns;
        $cellHeight = ($container->size->height - ($this->rows - 1) * $this->spacing) / $this->rows;

        foreach ($this->gridItems as $gridItem) {
            $x = $container->position->x + $gridItem->column * ($cellWidth + $this->spacing);
            $y = $container->position->y + $gridItem->row * ($cellHeight + $this->spacing);
            
            $width = $gridItem->columnSpan * $cellWidth + ($gridItem->columnSpan - 1) * $this->spacing;
            $height = $gridItem->rowSpan * $cellHeight + ($gridItem->rowSpan - 1) * $this->spacing;
            
            $position = new Position((int) $x, (int) $y);
            $size = new Size((int) $width, (int) $height);
            
            $areas[] = new Area($position, $size);
        }

        return $areas;
    }

    private function findNextAvailablePosition(): array
    {
        $occupied = [];
        foreach ($this->gridItems as $item) {
            for ($x = $item->column; $x < $item->column + $item->columnSpan; $x++) {
                for ($y = $item->row; $y < $item->row + $item->rowSpan; $y++) {
                    $occupied["{$x},{$y}"] = true;
                }
            }
        }

        for ($y = 0; $y < $this->rows; $y++) {
            for ($x = 0; $x < $this->columns; $x++) {
                if (!isset($occupied["{$x},{$y}"])) {
                    return ['x' => $x, 'y' => $y];
                }
            }
        }

        return ['x' => 0, 'y' => 0]; // Fallback
    }
}
