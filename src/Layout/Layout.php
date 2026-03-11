<?php declare(strict_types=1);

namespace PhpTuiDashboard\Layout;

use PhpTuiDashboard\Component;
use PhpTuiDashboard\Area;
use PhpTuiDashboard\Size;
use PhpTuiDashboard\Renderer;
use PhpTuiDashboard\Position;

abstract class Layout extends Component
{
    protected array $components = [];
    protected array $constraints = [];

    public function __construct(Position $position = new Position(0, 0), Size $size = new Size(0, 0))
    {
        parent::__construct($position, $size);
    }

    public function addComponent(Component $component, mixed $constraint = null): void
    {
        $this->components[] = $component;
        $this->constraints[] = $constraint;
    }

    public function removeComponent(Component $component): void
    {
        $index = array_search($component, $this->components, true);
        if ($index !== false) {
            array_splice($this->components, $index, 1);
            array_splice($this->constraints, $index, 1);
        }
    }

    public function getComponents(): array
    {
        return $this->components;
    }

    public function getComponentCount(): int
    {
        return count($this->components);
    }

    public function render(Renderer $renderer): void
    {
        $areas = $this->calculateAreas($this->getArea());
        
        foreach ($areas as $index => $area) {
            if (isset($this->components[$index])) {
                $component = $this->components[$index];
                $component->setPosition($area->position);
                $component->setSize($area->size);
                $component->render($renderer);
            }
        }
    }

    abstract public function calculateAreas(Area $container): array;

    protected function calculateFlexSizes(array $constraints, int $totalSize): array
    {
        $sizes = [];
        $flexTotal = 0;
        $fixedTotal = 0;

        // Calculate fixed sizes and flex totals
        foreach ($constraints as $constraint) {
            if (is_int($constraint)) {
                $fixedTotal += $constraint;
            } else {
                $flexTotal += $constraint ?? 1;
            }
        }

        $remainingSize = $totalSize - $fixedTotal;
        if ($remainingSize < 0) {
            $remainingSize = 0;
        }

        // Distribute remaining size among flex items
        foreach ($constraints as $constraint) {
            if (is_int($constraint)) {
                $sizes[] = $constraint;
            } else {
                $flex = $constraint ?? 1;
                $sizes[] = $flexTotal > 0 ? (int) (($remainingSize * $flex) / $flexTotal) : 0;
            }
        }

        return $sizes;
    }
}
