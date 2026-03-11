<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use PhpTuiDashboard\SoloScreenRenderer;
use PhpTuiDashboard\Position;
use PhpTuiDashboard\Size;
use PhpTuiDashboard\Area;
use PhpTuiDashboard\Layout\FlexLayout;
use PhpTuiDashboard\Layout\GridLayout;
use PhpTuiDashboard\Layout\FlexDirection;

// Demo components for layout testing
class DemoComponent extends \PhpTuiDashboard\Component
{
    private string $title;

    public function __construct(string $title, Position $position, Size $size)
    {
        $this->title = $title;
        parent::__construct($position, $size);
    }

    public function render(\PhpTuiDashboard\Renderer $renderer): void
    {
        $renderer->moveTo($this->position);
        $renderer->setStyle("\e[1;34m");
        $renderer->write($this->title);
        $renderer->setStyle(null);
        
        // Draw border
        $renderer->moveTo($this->position);
        $renderer->write("┌" . str_repeat("─", $this->size->width - 2) . "┐");
        
        for ($y = 1; $y < $this->size->height - 1; $y++) {
            $renderer->moveTo(new Position($this->position->x, $this->position->y + $y));
            $renderer->write("│" . str_repeat(" ", $this->size->width - 2) . "│");
        }
        
        if ($this->size->height > 1) {
            $renderer->moveTo(new Position($this->position->x, $this->position->y + $this->size->height - 1));
            $renderer->write("└" . str_repeat("─", $this->size->width - 2) . "┘");
        }
    }
}

// Test Flex Layout
echo "=== Flex Layout Demo ===\n\n";

$renderer = new SoloScreenRenderer(80, 24);
$flexLayout = new FlexLayout(FlexDirection::COLUMN, 1, 1);

$flexLayout->addComponent(new DemoComponent("Header", new Position(0, 0), new Size(78, 3)), 3);
$flexLayout->addComponent(new DemoComponent("Content", new Position(0, 0), new Size(78, 15)), 1);
$flexLayout->addComponent(new DemoComponent("Footer", new Position(0, 0), new Size(78, 3)), 3);

$container = new Area(new Position(1, 1), new Size(78, 22));
$areas = $flexLayout->calculateAreas($container);

foreach ($areas as $index => $area) {
    $component = $flexLayout->getComponents()[$index];
    $component->setPosition($area->position);
    $component->setSize($area->size);
    $component->render($renderer);
}

echo $renderer->getOutput();

echo "\n=== Grid Layout Demo ===\n\n";

$renderer2 = new SoloScreenRenderer(80, 24);
$gridLayout = new GridLayout(3, 2, 1);

$gridLayout->addComponent(new DemoComponent("CPU", new Position(0, 0), new Size(25, 10)));
$gridLayout->addComponent(new DemoComponent("Memory", new Position(0, 0), new Size(25, 10)));
$gridLayout->addComponent(new DemoComponent("Disk", new Position(0, 0), new Size(25, 10)));
$gridLayout->addComponent(new DemoComponent("Network", new Position(0, 0), new Size(25, 10)));

$container2 = new Area(new Position(1, 1), new Size(78, 22));
$areas2 = $gridLayout->calculateAreas($container2);

foreach ($areas2 as $index => $area) {
    $component = $gridLayout->getComponents()[$index];
    $component->setPosition($area->position);
    $component->setSize($area->size);
    $component->render($renderer2);
}

echo $renderer2->getOutput();
