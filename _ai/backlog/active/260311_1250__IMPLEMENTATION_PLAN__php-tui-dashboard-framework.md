---
filename: "_ai/backlog/active/260311_1250__IMPLEMENTATION_PLAN__php-tui-dashboard-framework.md"
title: "PHP TUI Dashboard Framework Implementation Plan"
createdAt: 2026-03-11 12:50
createdBy: Cascade [Penguin Alpha]
updatedAt: 2026-03-11 12:50
updatedBy: Cascade [Penguin Alpha]
status: draft
priority: high
tags: [php, tui, dashboard, framework, etl, widgets]
estimatedComplexity: moderate
documentType: IMPLEMENTATION_PLAN
---

# PHP TUI Dashboard Framework Implementation Plan

## Problem Statement

Create a lightweight PHP Terminal User Interface (TUI) framework for building tile-based dashboards, specifically designed for monitoring ETL (Extract, Transform, Load) processes. The framework should have minimal dependencies, provide flex/grid layout capabilities, and include demo widgets for logs, progress bars, system metrics, and a footer bar.

## Implementation Notes

### Current Project Context
- User wants to create a separate PHP TUI library inspired by golang's Tview's architecture
- Target use case: ETL application monitoring with live data updates

### Technical Requirements
- **Dependencies**: Minimal - preferably only Solo Screen (soloterm/screen) for terminal rendering
- **Architecture**: Component-based with flex/grid layout system
- **Performance**: Support for live data updates with differential rendering
- **Widgets**: Log viewer, progress bars, CPU/memory metrics, footer bar
- **Layout**: Tile-based dashboard engine

### Relevant Directories
- Current workspace: `/home/marc/devel/test-golang-tui`
- Target PHP library should be created as separate project structure

### Testing Commands
- PHP CLI execution: `php examples/dashboard.php`
- Dependency management: `composer require soloterm/screen`
- Performance testing with live data simulation

## Phase 1: Foundation and Core Architecture

### Objective
Establish the core framework structure, basic component system, and terminal rendering foundation.

### Tasks
1. **Project Structure Setup**
   - Create composer.json with minimal dependencies
   - Establish PSR-4 autoloading structure
   - Set up basic directory layout

2. **Core Component System**
   - Create abstract Component base class
   - Implement Position and Size value objects
   - Create basic rendering interface

3. **Terminal Rendering Layer**
   - Integrate Solo Screen for terminal output
   - Implement differential rendering support
   - Create screen management utilities

### Deliverables
- [NEW FILE] `composer.json` - Project dependencies and autoloading
- [NEW FILE] `src/Component.php` - Abstract base component class
- [NEW FILE] `src/Position.php` - Position value object
- [NEW FILE] `src/Size.php` - Size value object
- [NEW FILE] `src/Renderer.php` - Terminal rendering interface
- [NEW FILE] `src/SoloScreenRenderer.php` - Solo Screen implementation
- [NEW FILE] `examples/basic.php` - Basic rendering demonstration

### Source Code

**[NEW FILE] composer.json**
```json
{
    "name": "your-org/php-tui-dashboard",
    "description": "Lightweight PHP TUI framework for tile-based dashboards",
    "type": "library",
    "license": "MIT",
    "require": {
        "php": ">=8.1",
        "soloterm/screen": "^0.1.0"
    },
    "autoload": {
        "psr-4": {
            "PhpTuiDashboard\\": "src/"
        }
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0"
    }
}
```

**[NEW FILE] src/Component.php**
```php
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
```

**[NEW FILE] src/Position.php**
```php
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
```

**[NEW FILE] src/Size.php**
```php
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
```

**[NEW FILE] src/Area.php**
```php
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
```

**[NEW FILE] src/Renderer.php**
```php
<?php declare(strict_types=1);

namespace PhpTuiDashboard;

interface Renderer
{
    public function clear(): void;
    
    public function write(string $text): void;
    
    public function moveTo(Position $position): void;
    
    public function setStyle(?string $style): void;
    
    public function getOutput(?int $sequenceNumber = null): string;
    
    public function getLastRenderedSeqNo(): ?int;
    
    public function getSize(): Size;
}
```

**[NEW FILE] src/SoloScreenRenderer.php**
```php
<?php declare(strict_types=1);

namespace PhpTuiDashboard;

use SoloTerm\Screen\Screen;

class SoloScreenRenderer implements Renderer
{
    private Screen $screen;
    private ?string $currentStyle = null;

    public function __construct(int $width, int $height)
    {
        $this->screen = new Screen($width, $height);
    }

    public function clear(): void
    {
        $this->screen->write("\e[2J");
    }

    public function write(string $text): void
    {
        if ($this->currentStyle) {
            $text = $this->currentStyle . $text . "\e[0m";
        }
        $this->screen->write($text);
    }

    public function moveTo(Position $position): void
    {
        $this->screen->write("\e[{$position->y};{$position->x}H");
    }

    public function setStyle(?string $style): void
    {
        $this->currentStyle = $style;
    }

    public function getOutput(?int $sequenceNumber = null): string
    {
        return $this->screen->output($sequenceNumber);
    }

    public function getLastRenderedSeqNo(): ?int
    {
        return $this->screen->getLastRenderedSeqNo();
    }

    public function getSize(): Size
    {
        // For now, return fixed size - later we'll detect terminal size
        return new Size($this->screen->getWidth(), $this->screen->getHeight());
    }
}
```

**[NEW FILE] examples/basic.php**
```php
<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use PhpTuiDashboard\SoloScreenRenderer;
use PhpTuiDashboard\Position;

// Basic rendering demonstration
$renderer = new SoloScreenRenderer(80, 24);

$renderer->clear();
$renderer->moveTo(new Position(1, 1));
$renderer->setStyle("\e[1;34m");
$renderer->write("PHP TUI Dashboard Framework");
$renderer->setStyle(null);

$renderer->moveTo(new Position(1, 3));
$renderer->write("Basic rendering test with Solo Screen");

echo $renderer->getOutput();
```

### Verification Steps
1. Run `composer install` to install dependencies
2. Execute `php examples/basic.php` to verify basic rendering
3. Confirm terminal output displays styled text correctly
4. Test differential rendering capabilities

## Phase 2: Layout System Implementation

### Objective
Implement flexible layout system with flex and grid capabilities for tile-based dashboard arrangement.

### Tasks
1. **Layout Manager Foundation**
   - Create abstract Layout base class
   - Implement LayoutDirection enum
   - Create layout calculation utilities

2. **Flex Layout Implementation**
   - Build FlexLayout class for row/column arrangements
   - Implement size distribution algorithms
   - Add support for flexible and fixed sizing

3. **Grid Layout Implementation**
   - Create GridLayout for 2D tile arrangements
   - Implement column/row spanning
   - Add responsive grid behavior

### Deliverables
- [NEW FILE] `src/Layout/LayoutType.php` - Layout type enum
- [NEW FILE] `src/Layout/Layout.php` - Abstract layout base class
- [NEW FILE] `src/Layout/FlexLayout.php` - Flex layout implementation
- [NEW FILE] `src/Layout/GridLayout.php` - Grid layout implementation
- [NEW FILE] `src/Layout/LayoutCalculator.php` - Size calculation utilities
- [NEW FILE] `examples/layouts.php` - Layout demonstration

### Source Code

**[NEW FILE] src/Layout/LayoutType.php**
```php
<?php declare(strict_types=1);

namespace PhpTuiDashboard\Layout;

enum LayoutType
{
    case FLEX;
    case GRID;
    case ABSOLUTE;
}
```

**[NEW FILE] src/Layout/Layout.php**
```php
<?php declare(strict_types=1);

namespace PhpTuiDashboard\Layout;

use PhpTuiDashboard\Component;
use PhpTuiDashboard\Area;
use PhpTuiDashboard\Size;

abstract class Layout
{
    protected array $components = [];
    protected array $constraints = [];

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
```

**[NEW FILE] src/Layout/FlexLayout.php**
```php
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
        int $padding = 0
    ) {
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
```

**[NEW FILE] src/Layout/GridLayout.php**
```php
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

    public function __construct(int $columns = 2, int $rows = 2, int $spacing = 1)
    {
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
```

**[NEW FILE] examples/layouts.php**
```php
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
    $component = $flexLayout->components[$index];
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
    $component = $gridLayout->components[$index];
    $component->setPosition($area->position);
    $component->setSize($area->size);
    $component->render($renderer2);
}

echo $renderer2->getOutput();
```

### Verification Steps
1. Run `php examples/layouts.php` to verify layout demonstrations
2. Confirm flex layout distributes space correctly
3. Test grid layout positioning and spanning
4. Verify component boundaries and borders render properly

## Phase 3: Widget Components Implementation

### Objective
Create essential widget components for dashboard functionality: log viewer, progress bars, system metrics, and footer bar.

### Tasks
1. **Base Widget Framework**
   - Create Widget base class extending Component
   - Implement common widget functionality
   - Add widget styling utilities

2. **Log Widget Implementation**
   - Create scrolling log viewer
   - Implement log level filtering
   - Add auto-scroll and timestamp support

3. **Progress Bar Widgets**
   - Build horizontal and vertical progress bars
   - Implement multi-segment progress bars
   - Add percentage and status text

4. **System Metrics Widget**
   - Create CPU and memory monitoring widget
   - Implement gauge/visualization components
   - Add real-time data update support

5. **Footer Bar Widget**
   - Create status bar component
   - Implement sectioned layout
   - Add key binding hints and status indicators

### Deliverables
- [NEW FILE] `src/Widget/Widget.php` - Base widget class
- [NEW FILE] `src/Widget/LogWidget.php` - Log viewer widget
- [NEW FILE] `src/Widget/ProgressBar.php` - Progress bar widget
- [NEW FILE] `src/Widget/SystemMetricsWidget.php` - System metrics widget
- [NEW FILE] `src/Widget/FooterBar.php` - Footer bar widget
- [NEW FILE] `examples/widgets.php` - Widget demonstration
- [NEW FILE] `examples/dashboard.php` - Complete dashboard example

### Source Code

**[NEW FILE] src/Widget/Widget.php**
```php
<?php declare(strict_types=1);

namespace PhpTuiDashboard\Widget;

use PhpTuiDashboard\Component;
use PhpTuiDashboard\Renderer;
use PhpTuiDashboard\Position;
use PhpTuiDashboard\Size;

abstract class Widget extends Component
{
    protected string $title = '';
    protected bool $border = true;
    protected array $titleStyle = ['bold' => true, 'color' => 'blue'];
    protected array $borderStyle = ['color' => 'white'];

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function hasBorder(): bool
    {
        return $this->border;
    }

    public function setBorder(bool $border): void
    {
        $this->border = $border;
    }

    protected function renderBorder(Renderer $renderer): void
    {
        if (!$this->border || $this->size->height < 3 || $this->size->width < 3) {
            return;
        }

        $borderStyle = $this->getStyleString($this->borderStyle);
        $renderer->setStyle($borderStyle);

        // Top border with title
        $topBorder = "┌";
        if ($this->title) {
            $titlePadding = max(0, ($this->size->width - strlen($this->title) - 4) / 2);
            $titleLeft = (int) floor($titlePadding);
            $titleRight = (int) ceil($titlePadding);
            $topBorder .= str_repeat("─", $titleLeft) . " " . $this->title . " " . str_repeat("─", $titleRight);
        } else {
            $topBorder .= str_repeat("─", $this->size->width - 2);
        }
        $topBorder .= "┐";

        $renderer->moveTo($this->position);
        $renderer->write($topBorder);

        // Side borders
        for ($y = 1; $y < $this->size->height - 1; $y++) {
            $renderer->moveTo(new Position($this->position->x, $this->position->y + $y));
            $renderer->write("│");
            $renderer->moveTo(new Position($this->position->x + $this->size->width - 1, $this->position->y + $y));
            $renderer->write("│");
        }

        // Bottom border
        $renderer->moveTo(new Position($this->position->x, $this->position->y + $this->size->height - 1));
        $renderer->write("└" . str_repeat("─", $this->size->width - 2) . "┘");

        $renderer->setStyle(null);
    }

    protected function getInnerArea(): array
    {
        $innerX = $this->position->x + ($this->border ? 1 : 0);
        $innerY = $this->position->y + ($this->border ? 1 : 0);
        $innerWidth = $this->size->width - ($this->border ? 2 : 0);
        $innerHeight = $this->size->height - ($this->border ? 2 : 0);

        return [
            'position' => new Position($innerX, $innerY),
            'size' => new Size($innerWidth, $innerHeight)
        ];
    }

    protected function getStyleString(array $style): string
    {
        $codes = [];
        
        if ($style['bold'] ?? false) {
            $codes[] = '1';
        }
        
        if ($style['dim'] ?? false) {
            $codes[] = '2';
        }
        
        if ($style['italic'] ?? false) {
            $codes[] = '3';
        }
        
        if ($style['underline'] ?? false) {
            $codes[] = '4';
        }
        
        if (isset($style['color'])) {
            $colors = [
                'black' => '30', 'red' => '31', 'green' => '32', 'yellow' => '33',
                'blue' => '34', 'magenta' => '35', 'cyan' => '36', 'white' => '37'
            ];
            $codes[] = $colors[$style['color']] ?? '37';
        }
        
        if (isset($style['bgcolor'])) {
            $colors = [
                'black' => '40', 'red' => '41', 'green' => '42', 'yellow' => '43',
                'blue' => '44', 'magenta' => '45', 'cyan' => '46', 'white' => '47'
            ];
            $codes[] = $colors[$style['bgcolor']] ?? '40';
        }
        
        return empty($codes) ? '' : "\e[" . implode(';', $codes) . 'm';
    }

    protected function truncateText(string $text, int $maxLength): string
    {
        if (strlen($text) <= $maxLength) {
            return $text;
        }
        return substr($text, 0, $maxLength - 3) . '...';
    }
}
```

**[NEW FILE] src/Widget/LogWidget.php**
```php
<?php declare(strict_types=1);

namespace PhpTuiDashboard\Widget;

use PhpTuiDashboard\Renderer;
use PhpTuiDashboard\Position;
use PhpTuiDashboard\Size;

enum LogLevel
{
    case DEBUG;
    case INFO;
    case WARNING;
    case ERROR;
    case CRITICAL;

    public function getColor(): string
    {
        return match($this) {
            self::DEBUG => 'cyan',
            self::INFO => 'green',
            self::WARNING => 'yellow',
            self::ERROR => 'red',
            self::CRITICAL => 'magenta'
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::DEBUG => '🔍',
            self::INFO => 'ℹ️',
            self::WARNING => '⚠️',
            self::ERROR => '❌',
            self::CRITICAL => '🔥'
        };
    }
}

class LogEntry
{
    public function __construct(
        public readonly LogLevel $level,
        public readonly string $message,
        public readonly \DateTimeImmutable $timestamp,
        public readonly string $source = ''
    ) {}

    public function format(): string
    {
        $time = $this->timestamp->format('H:i:s');
        $source = $this->source ? "[{$this->source}] " : '';
        return "{$time} {$source}{$this->level->getIcon()} {$this->message}";
    }
}

class LogWidget extends Widget
{
    private array $logs = [];
    private int $maxLogs = 100;
    private int $scrollOffset = 0;
    private bool $autoScroll = true;
    private ?LogLevel $filterLevel = null;

    public function __construct(Position $position, Size $size, string $title = 'Logs')
    {
        parent::__construct($position, $size);
        $this->title = $title;
    }

    public function addLog(LogLevel $level, string $message, string $source = ''): void
    {
        $entry = new LogEntry($level, $message, new \DateTimeImmutable(), $source);
        $this->logs[] = $entry;
        
        // Keep only recent logs
        if (count($this->logs) > $this->maxLogs) {
            array_shift($this->logs);
        }
        
        // Auto-scroll to bottom if enabled
        if ($this->autoScroll) {
            $this->scrollOffset = max(0, count($this->logs) - $this->getVisibleLines());
        }
    }

    public function setFilterLevel(?LogLevel $level): void
    {
        $this->filterLevel = $level;
    }

    public function scrollUp(int $lines = 1): void
    {
        $this->scrollOffset = max(0, $this->scrollOffset - $lines);
        $this->autoScroll = false;
    }

    public function scrollDown(int $lines = 1): void
    {
        $maxOffset = max(0, count($this->logs) - $this->getVisibleLines());
        $this->scrollOffset = min($maxOffset, $this->scrollOffset + $lines);
        $this->autoScroll = $this->scrollOffset >= $maxOffset;
    }

    public function render(Renderer $renderer): void
    {
        $this->renderBorder($renderer);
        
        ['position' => $innerPos, 'size' => $innerSize] = $this->getInnerArea();
        
        if ($innerSize->height <= 0 || $innerSize->width <= 0) {
            return;
        }

        // Filter logs
        $filteredLogs = $this->logs;
        if ($this->filterLevel) {
            $filteredLogs = array_filter($filteredLogs, fn($log) => $log->level >= $this->filterLevel);
        }

        // Display visible logs
        $visibleLogs = array_slice($filteredLogs, $this->scrollOffset, $this->getVisibleLines());
        
        foreach ($visibleLogs as $index => $log) {
            $y = $innerPos->y + $index;
            if ($y >= $innerPos->y + $innerSize->height) {
                break;
            }

            $renderer->moveTo(new Position($innerPos->x, $y));
            $renderer->setStyle($this->getStyleString(['color' => $log->level->getColor()]));
            
            $formatted = $this->truncateText($log->format(), $innerSize->width);
            $renderer->write($formatted);
            $renderer->setStyle(null);
        }

        // Show scroll indicator if needed
        if (count($filteredLogs) > $this->getVisibleLines()) {
            $scrollPos = $innerPos->x + $innerSize->width - 1;
            $scrollHeight = $innerSize->height;
            $scrollProgress = $this->scrollOffset / max(1, count($filteredLogs) - $this->getVisibleLines());
            $thumbPos = (int) ($scrollProgress * ($scrollHeight - 1));
            
            for ($y = 0; $y < $scrollHeight; $y++) {
                $renderer->moveTo(new Position($scrollPos, $innerPos->y + $y));
                $char = ($y === $thumbPos) ? '█' : '░';
                $renderer->write($char);
            }
        }
    }

    private function getVisibleLines(): int
    {
        ['size' => $innerSize] = $this->getInnerArea();
        return max(0, $innerSize->height);
    }
}
```

**[NEW FILE] src/Widget/ProgressBar.php**
```php
<?php declare(strict_types=1);

namespace PhpTuiDashboard\Widget;

use PhpTuiDashboard\Renderer;
use PhpTuiDashboard\Position;
use PhpTuiDashboard\Size;

class ProgressSegment
{
    public function __construct(
        public readonly float $value,
        public readonly string $color,
        public readonly ?string $label = null
    ) {}
}

class ProgressBar extends Widget
{
    private float $progress = 0.0;
    private array $segments = [];
    private bool $showPercentage = true;
    private bool $showLabel = false;
    private string $label = '';
    private string $progressChar = '█';
    private string $emptyChar = '░';

    public function __construct(Position $position, Size $size, string $title = 'Progress')
    {
        parent::__construct($position, $size);
        $this->title = $title;
    }

    public function setProgress(float $progress): void
    {
        $this->progress = max(0.0, min(1.0, $progress));
    }

    public function setSegments(array $segments): void
    {
        $this->segments = $segments;
        $total = array_sum(array_map(fn($s) => $s->value, $segments));
        if ($total > 0) {
            $this->progress = min(1.0, $total);
        }
    }

    public function setShowPercentage(bool $show): void
    {
        $this->showPercentage = $show;
    }

    public function setShowLabel(bool $show): void
    {
        $this->showLabel = $show;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function render(Renderer $renderer): void
    {
        $this->renderBorder($renderer);
        
        ['position' => $innerPos, 'size' => $innerSize] = $this->getInnerArea();
        
        if ($innerSize->height <= 0 || $innerSize->width <= 0) {
            return;
        }

        $barY = $innerPos->y + (int) ($innerSize->height / 2);
        $barWidth = $innerSize->width;
        
        // Adjust width if showing label/percentage
        if ($this->showLabel && !empty($this->label)) {
            $barWidth = max(10, $barWidth - strlen($this->label) - 1);
        }
        if ($this->showPercentage) {
            $barWidth = max(5, $barWidth - 5);
        }

        // Render progress bar
        $renderer->moveTo(new Position($innerPos->x, $barY));
        
        if (empty($this->segments)) {
            // Simple progress bar
            $filledWidth = (int) ($this->progress * $barWidth);
            $renderer->write(str_repeat($this->progressChar, $filledWidth));
            $renderer->write(str_repeat($this->emptyChar, $barWidth - $filledWidth));
        } else {
            // Segmented progress bar
            $currentX = 0;
            foreach ($this->segments as $segment) {
                $segmentWidth = (int) (($segment->value / $this->progress) * $this->progress * $barWidth);
                $renderer->setStyle($this->getStyleString(['color' => $segment->color]));
                $renderer->write(str_repeat($this->progressChar, $segmentWidth));
                $currentX += $segmentWidth;
            }
            $renderer->setStyle(null);
            $renderer->write(str_repeat($this->emptyChar, $barWidth - $currentX));
        }

        // Show percentage
        if ($this->showPercentage) {
            $percentage = (int) ($this->progress * 100);
            $percentageText = sprintf("%3d%%", $percentage);
            $renderer->moveTo(new Position($innerPos->x + $barWidth, $barY));
            $renderer->write($percentageText);
        }

        // Show label
        if ($this->showLabel && !empty($this->label)) {
            $labelX = $innerPos->x + $innerSize->width - strlen($this->label);
            $renderer->moveTo(new Position($labelX, $barY));
            $renderer->write($this->label);
        }
    }
}
```

**[NEW FILE] src/Widget/SystemMetricsWidget.php**
```php
<?php declare(strict_types=1);

namespace PhpTuiDashboard\Widget;

use PhpTuiDashboard\Renderer;
use PhpTuiDashboard\Position;
use PhpTuiDashboard\Size;

class SystemMetric
{
    public function __construct(
        public readonly string $name,
        public readonly float $value,
        public readonly float $maxValue,
        public readonly string $unit = '',
        public readonly string $color = 'blue'
    ) {}

    public function getPercentage(): float
    {
        return $this->maxValue > 0 ? ($this->value / $this->maxValue) : 0.0;
    }
}

class SystemMetricsWidget extends Widget
{
    private array $metrics = [];
    private bool $showGauges = true;
    private bool $showValues = true;

    public function __construct(Position $position, Size $size, string $title = 'System Metrics')
    {
        parent::__construct($position, $size);
        $this->title = $title;
    }

    public function addMetric(SystemMetric $metric): void
    {
        $this->metrics[] = $metric;
    }

    public function setMetrics(array $metrics): void
    {
        $this->metrics = $metrics;
    }

    public function setShowGauges(bool $show): void
    {
        $this->showGauges = $show;
    }

    public function setShowValues(bool $show): void
    {
        $this->showValues = $show;
    }

    public function render(Renderer $renderer): void
    {
        $this->renderBorder($renderer);
        
        ['position' => $innerPos, 'size' => $innerSize] = $this->getInnerArea();
        
        if ($innerSize->height <= 0 || $innerSize->width <= 0) {
            return;
        }

        $lineHeight = 2;
        $maxMetrics = min(count($this->metrics), (int) ($innerSize->height / $lineHeight));

        for ($i = 0; $i < $maxMetrics; $i++) {
            $metric = $this->metrics[$i];
            $y = $innerPos->y + ($i * $lineHeight);

            // Metric name
            $renderer->moveTo(new Position($innerPos->x, $y));
            $renderer->setStyle($this->getStyleString(['bold' => true]));
            $nameText = $this->truncateText($metric->name, 15);
            $renderer->write($nameText);
            $renderer->setStyle(null);

            // Gauge
            if ($this->showGauges) {
                $gaugeWidth = 20;
                $gaugeX = $innerPos->x + 16;
                $filledWidth = (int) ($metric->getPercentage() * $gaugeWidth);
                
                $renderer->moveTo(new Position($gaugeX, $y));
                $renderer->write('[');
                $renderer->setStyle($this->getStyleString(['color' => $metric->color]));
                $renderer->write(str_repeat('█', $filledWidth));
                $renderer->setStyle(null);
                $renderer->write(str_repeat('░', $gaugeWidth - $filledWidth));
                $renderer->write(']');
            }

            // Value
            if ($this->showValues) {
                $valueX = $innerPos->x + ($this->showGauges ? 38 : 16);
                $valueText = sprintf("%.1f%s", $metric->value, $metric->unit);
                $renderer->moveTo(new Position($valueX, $y));
                $renderer->write($valueText);
            }
        }
    }
}
```

**[NEW FILE] src/Widget/FooterBar.php**
```php
<?php declare(strict_types=1);

namespace PhpTuiDashboard\Widget;

use PhpTuiDashboard\Renderer;
use PhpTuiDashboard\Position;
use PhpTuiDashboard\Size;

class FooterSection
{
    public function __construct(
        public readonly string $text,
        public readonly array $style = [],
        public readonly int $minWidth = 0
    ) {}
}

class FooterBar extends Widget
{
    private array $sections = [];
    private string $separator = ' │ ';
    private bool $border = false;

    public function __construct(Position $position, Size $size)
    {
        parent::__construct($position, $size);
        $this->border = false; // Footer typically doesn't have border
    }

    public function addSection(FooterSection $section): void
    {
        $this->sections[] = $section;
    }

    public function addTextSection(string $text, array $style = [], int $minWidth = 0): void
    {
        $this->addSection(new FooterSection($text, $style, $minWidth));
    }

    public function setSeparator(string $separator): void
    {
        $this->separator = $separator;
    }

    public function render(Renderer $renderer): void
    {
        if ($this->border) {
            $this->renderBorder($renderer);
        }
        
        ['position' => $innerPos, 'size' => $innerSize] = $this->getInnerArea();
        
        if ($innerSize->height <= 0 || $innerSize->width <= 0) {
            return;
        }

        // Calculate available space for sections
        $separatorCount = count($this->sections) > 1 ? count($this->sections) - 1 : 0;
        $separatorWidth = strlen($this->separator) * $separatorCount;
        $minWidths = array_sum(array_map(fn($s) => $s->minWidth, $this->sections));
        $availableWidth = $innerSize->width - $separatorWidth;

        // Calculate section widths
        $sectionWidths = [];
        $flexSections = [];
        $totalFlexWeight = 0;

        foreach ($this->sections as $index => $section) {
            if ($section->minWidth > 0) {
                $sectionWidths[$index] = max($section->minWidth, strlen($section->text));
                $availableWidth -= $sectionWidths[$index];
            } else {
                $flexSections[] = $index;
                $totalFlexWeight += 1;
            }
        }

        // Distribute remaining width among flex sections
        foreach ($flexSections as $index) {
            $sectionWidths[$index] = $totalFlexWeight > 0 
                ? (int) ($availableWidth / $totalFlexWeight)
                : 0;
        }

        // Render sections
        $currentX = $innerPos->x;
        $y = $innerPos->y + (int) ($innerSize->height / 2);

        foreach ($this->sections as $index => $section) {
            $width = $sectionWidths[$index] ?? 0;
            
            $renderer->moveTo(new Position($currentX, $y));
            $renderer->setStyle($this->getStyleString($section->style));
            
            $text = $this->truncateText($section->text, $width);
            if ($width > strlen($text)) {
                $text = str_pad($text, $width);
            }
            
            $renderer->write($text);
            $renderer->setStyle(null);
            
            $currentX += $width;
            
            // Add separator (except after last section)
            if ($index < count($this->sections) - 1) {
                $renderer->moveTo(new Position($currentX, $y));
                $renderer->write($this->separator);
                $currentX += strlen($this->separator);
            }
        }
    }
}
```

**[NEW FILE] examples/widgets.php**
```php
<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use PhpTuiDashboard\SoloScreenRenderer;
use PhpTuiDashboard\Position;
use PhpTuiDashboard\Size;
use PhpTuiDashboard\Widget\LogWidget;
use PhpTuiDashboard\Widget\ProgressBar;
use PhpTuiDashboard\Widget\SystemMetricsWidget;
use PhpTuiDashboard\Widget\FooterBar;
use PhpTuiDashboard\Widget\LogLevel;
use PhpTuiDashboard\Widget\SystemMetric;

// Create renderer
$renderer = new SoloScreenRenderer(80, 24);

// Log Widget Demo
echo "=== Log Widget Demo ===\n\n";

$logWidget = new LogWidget(new Position(1, 1), new Size(78, 10), "Application Logs");
$logWidget->addLog(LogLevel::INFO, "Application started successfully", "main");
$logWidget->addLog(LogLevel::DEBUG, "Loading configuration file", "config");
$logWidget->addLog(LogLevel::WARNING, "High memory usage detected", "monitor");
$logWidget->addLog(LogLevel::ERROR, "Failed to connect to database", "db");
$logWidget->addLog(LogLevel::INFO, "Retrying connection...", "db");
$logWidget->addLog(LogLevel::INFO, "Database connection established", "db");

$logWidget->render($renderer);
echo $renderer->getOutput();

echo "\n=== Progress Bar Demo ===\n\n";

$renderer2 = new SoloScreenRenderer(80, 24);

// Simple progress bar
$progress1 = new ProgressBar(new Position(1, 1), new Size(40, 3), "File Download");
$progress1->setProgress(0.75);
$progress1->setLabel("file.zip");

// Segmented progress bar
$progress2 = new ProgressBar(new Position(1, 5), new Size(40, 3), "Task Progress");
$progress2->setSegments([
    new ProgressSegment(0.3, 'green', 'Completed'),
    new ProgressSegment(0.2, 'yellow', 'In Progress'),
    new ProgressSegment(0.1, 'red', 'Failed')
]);

$progress1->render($renderer2);
$progress2->render($renderer2);
echo $renderer2->getOutput();

echo "\n=== System Metrics Demo ===\n\n";

$renderer3 = new SoloScreenRenderer(80, 24);

$metricsWidget = new SystemMetricsWidget(new Position(1, 1), new Size(50, 8), "System Resources");
$metricsWidget->addMetric(new SystemMetric("CPU", 65.5, 100.0, "%", "blue"));
$metricsWidget->addMetric(new SystemMetric("Memory", 4.2, 8.0, "GB", "green"));
$metricsWidget->addMetric(new SystemMetric("Disk", 120.5, 500.0, "GB", "yellow"));
$metricsWidget->addMetric(new SystemMetric("Network", 15.2, 100.0, "Mbps", "cyan"));

$metricsWidget->render($renderer3);
echo $renderer3->getOutput();

echo "\n=== Footer Bar Demo ===\n\n";

$renderer4 = new SoloScreenRenderer(80, 24);

$footer = new FooterBar(new Position(1, 1), new Size(78, 1));
$footer->addTextSection("F1:Help", ['bold' => true]);
$footer->addTextSection("F2:Settings");
$footer->addTextSection("q:Quit", ['color' => 'red']);
$footer->addTextSection("Status: Running", ['color' => 'green'], 15);

$footer->render($renderer4);
echo $renderer4->getOutput();
```

**[NEW FILE] examples/dashboard.php**
```php
<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use PhpTuiDashboard\SoloScreenRenderer;
use PhpTuiDashboard\Position;
use PhpTuiDashboard\Size;
use PhpTuiDashboard\Layout\FlexLayout;
use PhpTuiDashboard\Layout\FlexDirection;
use PhpTuiDashboard\Widget\LogWidget;
use PhpTuiDashboard\Widget\ProgressBar;
use PhpTuiDashboard\Widget\SystemMetricsWidget;
use PhpTuiDashboard\Widget\FooterBar;
use PhpTuiDashboard\Widget\LogLevel;
use PhpTuiDashboard\Widget\SystemMetric;

// Create dashboard
class ETLDashboard
{
    private SoloScreenRenderer $renderer;
    private FlexLayout $mainLayout;
    private FlexLayout $contentLayout;
    private LogWidget $logWidget;
    private SystemMetricsWidget $metricsWidget;
    private array $progressBars = [];
    private FooterBar $footerBar;

    public function __construct()
    {
        $this->renderer = new SoloScreenRenderer(80, 24);
        $this->setupLayout();
        $this->setupWidgets();
    }

    private function setupLayout(): void
    {
        // Main vertical layout
        $this->mainLayout = new FlexLayout(FlexDirection::COLUMN, 1, 1);
        
        // Content horizontal layout
        $this->contentLayout = new FlexLayout(FlexDirection::ROW, 1, 1);
    }

    private function setupWidgets(): void
    {
        // System metrics widget
        $this->metricsWidget = new SystemMetricsWidget(
            new Position(0, 0), 
            new Size(40, 12), 
            "System Resources"
        );
        
        // Add sample metrics
        $this->metricsWidget->addMetric(new SystemMetric("CPU Usage", 45.2, 100.0, "%", "blue"));
        $this->metricsWidget->addMetric(new SystemMetric("Memory", 3.8, 8.0, "GB", "green"));
        $this->metricsWidget->addMetric(new SystemMetric("Disk I/O", 125.6, 500.0, "MB/s", "yellow"));
        $this->metricsWidget->addMetric(new SystemMetric("Network", 25.3, 100.0, "Mbps", "cyan"));

        // Log widget
        $this->logWidget = new LogWidget(
            new Position(0, 0), 
            new Size(40, 12), 
            "ETL Process Logs"
        );
        
        // Add sample logs
        $this->logWidget->addLog(LogLevel::INFO, "ETL dashboard started", "system");
        $this->logWidget->addLog(LogLevel::INFO, "Starting import process", "import");
        $this->logWidget->addLog(LogLevel::DEBUG, "Reading source file: data.csv", "import");
        $this->logWidget->addLog(LogLevel::INFO, "Processing 1,250 records", "transform");
        $this->logWidget->addLog(LogLevel::WARNING, "Duplicate record found, skipping", "transform");
        $this->logWidget->addLog(LogLevel::INFO, "Exporting to target database", "export");

        // Progress bars for ETL stages
        $this->progressBars['import'] = new ProgressBar(
            new Position(0, 0), 
            new Size(78, 3), 
            "Import Progress"
        );
        $this->progressBars['transform'] = new ProgressBar(
            new Position(0, 0), 
            new Size(78, 3), 
            "Transform Progress"
        );
        $this->progressBars['export'] = new ProgressBar(
            new Position(0, 0), 
            new Size(78, 3), 
            "Export Progress"
        );

        // Footer bar
        $this->footerBar = new FooterBar(new Position(0, 0), new Size(80, 1));
        $this->footerBar->addTextSection("F1:Help", ['bold' => true]);
        $this->footerBar->addTextSection("r:Refresh", ['bold' => true]);
        $this->footerBar->addTextSection("l:Logs", ['bold' => true]);
        $this->footerBar->addTextSection("q:Quit", ['color' => 'red']);
        $this->footerBar->addTextSection("Last Update: " . date('H:i:s'), ['color' => 'green'], 20);
    }

    public function render(): void
    {
        $this->renderer->clear();
        
        // Add widgets to content layout
        $this->contentLayout->addComponent($this->metricsWidget, 1);
        $this->contentLayout->addComponent($this->logWidget, 1);
        
        // Add components to main layout
        $this->mainLayout->addComponent($this->progressBars['import'], 3);
        $this->mainLayout->addComponent($this->contentLayout, 1);
        $this->mainLayout->addComponent($this->progressBars['transform'], 3);
        $this->mainLayout->addComponent($this->progressBars['export'], 3);
        $this->mainLayout->addComponent($this->footerBar, 1);

        // Calculate layout areas
        $container = new \PhpTuiDashboard\Area(
            new Position(1, 1), 
            new Size(78, 22)
        );
        
        $areas = $this->mainLayout->calculateAreas($container);
        
        // Position and render all components
        foreach ($areas as $index => $area) {
            $component = $this->mainLayout->components[$index];
            $component->setPosition($area->position);
            $component->setSize($area->size);
            $component->render($this->renderer);
        }
        
        echo $this->renderer->getOutput();
    }

    public function simulateProgress(): void
    {
        // Simulate ETL progress
        $importProgress = 0.0;
        $transformProgress = 0.0;
        $exportProgress = 0.0;

        for ($i = 0; $i <= 100; $i += 5) {
            $this->renderer->clear();
            
            // Update progress
            $importProgress = min(1.0, $i / 100.0);
            $transformProgress = min(1.0, max(0.0, ($i - 20) / 100.0));
            $exportProgress = min(1.0, max(0.0, ($i - 50) / 100.0));
            
            $this->progressBars['import']->setProgress($importProgress);
            $this->progressBars['transform']->setProgress($transformProgress);
            $this->progressBars['export']->setProgress($exportProgress);
            
            // Add logs occasionally
            if ($i % 25 === 0 && $i > 0) {
                $this->logWidget->addLog(
                    LogLevel::INFO, 
                    "Progress: Import " . (int)($importProgress * 100) . "%, Transform " . (int)($transformProgress * 100) . "%, Export " . (int)($exportProgress * 100) . "%",
                    "progress"
                );
            }
            
            // Update metrics
            $cpu = 45.2 + rand(-10, 15);
            $memory = 3.8 + rand(-1, 2);
            $this->metricsWidget->setMetrics([
                new SystemMetric("CPU Usage", $cpu, 100.0, "%", "blue"),
                new SystemMetric("Memory", $memory, 8.0, "GB", "green"),
                new SystemMetric("Disk I/O", 125.6 + rand(-20, 30), 500.0, "MB/s", "yellow"),
                new SystemMetric("Network", 25.3 + rand(-5, 10), 100.0, "Mbps", "cyan")
            ]);
            
            $this->render();
            
            if ($i < 100) {
                usleep(200000); // 200ms delay
                system('clear'); // Clear screen for next frame
            }
        }
        
        $this->logWidget->addLog(LogLevel::INFO, "ETL process completed successfully!", "system");
        $this->render();
    }
}

// Run the dashboard
$dashboard = new ETLDashboard();
$dashboard->simulateProgress();
```

### Verification Steps
1. Run `php examples/widgets.php` to verify individual widget functionality
2. Execute `php examples/dashboard.php` to see complete ETL dashboard
3. Test widget interactions (scrolling, progress updates)
4. Verify layout responsiveness and rendering performance
5. Confirm differential rendering works for live updates

## Phase 4: Application Framework and Event System

### Objective
Create the main application framework with event handling, keyboard input, and real-time update capabilities.

### Tasks
1. **Application Core**
   - Create Application main class
   - Implement event loop management
   - Add terminal size detection

2. **Event System**
   - Build event dispatcher and listener system
   - Implement keyboard input handling
   - Create custom event types

3. **Real-time Updates**
   - Add timer/clock for periodic updates
   - Implement data source integration
   - Create update scheduling system

4. **Input Handling**
   - Implement keyboard event processing
   - Add mouse support (if needed)
   - Create command binding system

### Deliverables
- [NEW FILE] `src/Application.php` - Main application class
- [NEW FILE] `src/Event/Event.php` - Base event interface
- [NEW FILE] `src/Event/KeyboardEvent.php` - Keyboard input events
- [NEW FILE] `src/Event/TimerEvent.php` - Timer events
- [NEW FILE] `src/Event/EventDispatcher.php` - Event management
- [NEW FILE] `src/Input/InputHandler.php` - Input processing
- [NEW FILE] `examples/interactive.php` - Interactive dashboard example
- [NEW FILE] `examples/etl-monitor.php` - Complete ETL monitor application

### Source Code

**[NEW FILE] src/Application.php**
```php
<?php declare(strict_types=1);

namespace PhpTuiDashboard;

use PhpTuiDashboard\Event\EventDispatcher;
use PhpTuiDashboard\Event\TimerEvent;
use PhpTuiDashboard\Input\InputHandler;
use PhpTuiDashboard\Layout\FlexLayout;
use PhpTuiDashboard\Layout\FlexDirection;

class Application
{
    private SoloScreenRenderer $renderer;
    private EventDispatcher $eventDispatcher;
    private InputHandler $inputHandler;
    private FlexLayout $layout;
    private array $components = [];
    private bool $running = false;
    private int $fps = 10;
    private ?\Closure $updateCallback = null;

    public function __construct(int $width = 80, int $height = 24)
    {
        $this->renderer = new SoloScreenRenderer($width, $height);
        $this->eventDispatcher = new EventDispatcher();
        $this->inputHandler = new InputHandler();
        $this->layout = new FlexLayout(FlexDirection::COLUMN, 1, 0);
        
        $this->setupEventHandlers();
    }

    public function addComponent(Component $component, mixed $constraint = null): void
    {
        $this->components[] = $component;
        $this->layout->addComponent($component, $constraint);
    }

    public function setUpdateCallback(\Closure $callback): void
    {
        $this->updateCallback = $callback;
    }

    public function setFPS(int $fps): void
    {
        $this->fps = max(1, $fps);
    }

    public function run(): void
    {
        $this->running = true;
        $this->enableRawMode();
        
        $lastRender = 0;
        $frameInterval = 1000000 / $this->fps; // microseconds
        
        while ($this->running) {
            $startTime = microtime(true);
            
            // Handle input
            $this->inputHandler->processInput($this->eventDispatcher);
            
            // Process events
            $this->eventDispatcher->dispatchEvents();
            
            // Update data
            if ($this->updateCallback) {
                ($this->updateCallback)($this);
            }
            
            // Render frame
            $currentTime = (int) (microtime(true) * 1000000);
            if ($currentTime - $lastRender >= $frameInterval) {
                $this->render();
                $lastRender = $currentTime;
            }
            
            // Sleep to maintain FPS
            $elapsed = (microtime(true) - $startTime) * 1000000;
            if ($elapsed < $frameInterval) {
                usleep((int) ($frameInterval - $elapsed));
            }
        }
        
        $this->disableRawMode();
    }

    public function stop(): void
    {
        $this->running = false;
    }

    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }

    public function getRenderer(): SoloScreenRenderer
    {
        return $this->renderer;
    }

    private function render(): void
    {
        $this->renderer->clear();
        
        // Calculate layout
        $container = new Area(
            new Position(0, 0),
            $this->renderer->getSize()
        );
        
        $areas = $this->layout->calculateAreas($container);
        
        // Position and render components
        foreach ($areas as $index => $area) {
            if (isset($this->components[$index])) {
                $component = $this->components[$index];
                $component->setPosition($area->position);
                $component->setSize($area->size);
                $component->render($this->renderer);
            }
        }
        
        // Output to terminal
        echo $this->renderer->getOutput();
    }

    private function setupEventHandlers(): void
    {
        // Handle quit events
        $this->eventDispatcher->addListener('keyboard.q', function($event) {
            $this->stop();
        });
        
        $this->eventDispatcher->addListener('keyboard.ctrl_c', function($event) {
            $this->stop();
        });
        
        // Handle window resize
        $this->eventDispatcher->addListener('system.resize', function($event) {
            $this->handleResize($event->getData()['width'], $event->getData()['height']);
        });
    }

    private function handleResize(int $width, int $height): void
    {
        // Recreate renderer with new size
        $this->renderer = new SoloScreenRenderer($width, $height);
    }

    private function enableRawMode(): void
    {
        // Enable raw terminal mode for input handling
        system('stty -icanon -echo');
    }

    private function disableRawMode(): void
    {
        // Restore normal terminal mode
        system('stty icanon echo');
    }
}
```

**[NEW FILE] src/Event/Event.php**
```php
<?php declare(strict_types=1);

namespace PhpTuiDashboard\Event;

interface Event
{
    public function getType(): string;
    public function getData(): array;
    public function getTimestamp(): float;
}
```

**[NEW FILE] src/Event/KeyboardEvent.php**
```php
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
```

**[NEW FILE] src/Event/TimerEvent.php**
```php
<?php declare(strict_types=1);

namespace PhpTuiDashboard\Event;

class TimerEvent implements Event
{
    private string $type;
    private array $data;
    private float $timestamp;

    public function __construct(string $timerId, array $data = [])
    {
        $this->type = 'timer.' . $timerId;
        $this->data = array_merge(['timerId' => $timerId], $data);
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

    public function getTimerId(): string
    {
        return $this->data['timerId'];
    }
}
```

**[NEW FILE] src/Event/EventDispatcher.php**
```php
<?php declare(strict_types=1);

namespace PhpTuiDashboard\Event;

class EventDispatcher
{
    private array $listeners = [];
    private array $eventQueue = [];

    public function addListener(string $eventType, callable $listener): void
    {
        $this->listeners[$eventType][] = $listener;
    }

    public function removeListener(string $eventType, callable $listener): void
    {
        if (!isset($this->listeners[$eventType])) {
            return;
        }
        
        $this->listeners[$eventType] = array_filter(
            $this->listeners[$eventType],
            fn($l) => $l !== $listener
        );
    }

    public function dispatch(Event $event): void
    {
        $this->eventQueue[] = $event;
    }

    public function dispatchEvents(): void
    {
        while (!empty($this->eventQueue)) {
            $event = array_shift($this->eventQueue);
            $this->processEvent($event);
        }
    }

    private function processEvent(Event $event): void
    {
        $eventType = $event->getType();
        
        // Call specific listeners
        if (isset($this->listeners[$eventType])) {
            foreach ($this->listeners[$eventType] as $listener) {
                $listener($event);
            }
        }
        
        // Call wildcard listeners
        $wildcardType = substr($eventType, 0, strrpos($eventType, '.'));
        if (isset($this->listeners[$wildcardType . '.*'])) {
            foreach ($this->listeners[$wildcardType . '.*'] as $listener) {
                $listener($event);
            }
        }
    }
}
```

**[NEW FILE] src/Input/InputHandler.php**
```php
<?php declare(strict_types=1);

namespace PhpTuiDashboard\Input;

use PhpTuiDashboard\Event\EventDispatcher;
use PhpTuiDashboard\Event\KeyboardEvent;

class InputHandler
{
    private bool $reading = false;

    public function processInput(EventDispatcher $eventDispatcher): void
    {
        // Non-blocking input check
        $read = [STDIN];
        $write = [];
        $except = [];
        
        if (stream_select($read, $write, $except, 0, 0) > 0) {
            $char = fread(STDIN, 1);
            
            if ($char === false || $char === '') {
                return;
            }
            
            $this->handleCharacter($char, $eventDispatcher);
        }
    }

    private function handleCharacter(string $char, EventDispatcher $eventDispatcher): void
    {
        // Handle special keys and combinations
        switch ($char) {
            case "\03": // ESC or start of escape sequence
                $this->handleEscapeSequence($eventDispatcher);
                break;
                
            case "\t":
                $eventDispatcher->dispatch(new KeyboardEvent('tab'));
                break;
                
            case "\n":
            case "\r":
                $eventDispatcher->dispatch(new KeyboardEvent('enter'));
                break;
                
            case "\x7F": // Backspace
                $eventDispatcher->dispatch(new KeyboardEvent('backspace'));
                break;
                
            case "\x04": // Ctrl+D
                $eventDispatcher->dispatch(new KeyboardEvent('ctrl_d'));
                break;
                
            case "\x03": // Ctrl+C
                $eventDispatcher->dispatch(new KeyboardEvent('ctrl_c'));
                break;
                
            default:
                // Regular printable characters
                if (ord($char) >= 32 && ord($char) <= 126) {
                    $eventDispatcher->dispatch(new KeyboardEvent($char));
                }
                break;
        }
    }

    private function handleEscapeSequence(EventDispatcher $eventDispatcher): void
    {
        // Read additional characters to determine the escape sequence
        $seq = '';
        $timeout = microtime(true) + 0.1; // 100ms timeout
        
        while (microtime(true) < $timeout) {
            $read = [STDIN];
            if (stream_select($read, $write, $except, 0, 10000) > 0) {
                $char = fread(STDIN, 1);
                if ($char === false || $char === '') {
                    break;
                }
                $seq .= $char;
                
                // Check for common escape sequences
                if (str_starts_with($seq, '[')) {
                    if (preg_match('/^\[([ABCDEFHP])$/', $seq, $matches)) {
                        $this->handleArrowKey($matches[1], $eventDispatcher);
                        return;
                    }
                }
                
                if (strlen($seq) >= 4) {
                    break; // Too long, probably not a valid sequence
                }
            }
        }
        
        // If we couldn't identify a specific sequence, treat as ESC
        $eventDispatcher->dispatch(new KeyboardEvent('escape'));
    }

    private function handleArrowKey(string $key, EventDispatcher $eventDispatcher): void
    {
        $arrowKeys = [
            'A' => 'up',
            'B' => 'down', 
            'C' => 'right',
            'D' => 'left',
            'H' => 'home',
            'F' => 'end'
        ];
        
        if (isset($arrowKeys[$key])) {
            $eventDispatcher->dispatch(new KeyboardEvent($arrowKeys[$key]));
        }
    }
}
```

**[NEW FILE] examples/interactive.php**
```php
<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use PhpTuiDashboard\Application;
use PhpTuiDashboard\Position;
use PhpTuiDashboard\Size;
use PhpTuiDashboard\Widget\LogWidget;
use PhpTuiDashboard\Widget\SystemMetricsWidget;
use PhpTuiDashboard\Widget\FooterBar;
use PhpTuiDashboard\Widget\LogLevel;
use PhpTuiDashboard\Widget\SystemMetric;

// Create interactive application
$app = new Application(80, 24);
$app->setFPS(15);

// Create components
$logWidget = new LogWidget(new Position(0, 0), new Size(40, 12), "Interactive Logs");
$metricsWidget = new SystemMetricsWidget(new Position(0, 0), new Size(40, 12), "Live Metrics");
$footerBar = new FooterBar(new Position(0, 0), new Size(80, 1));

// Setup footer
$footerBar->addTextSection("q:Quit", ['color' => 'red']);
$footerBar->addTextSection("r:Refresh", ['bold' => true]);
$footerBar->addTextSection("c:Clear Logs", ['bold' => true]);
$footerBar->addTextSection("Space:Pause", ['bold' => true]);

// Add components to application
$app->addComponent($logWidget, 1);
$app->addComponent($metricsWidget, 1);
$app->addComponent($footerBar, 1);

// Setup event handlers
$eventDispatcher = $app->getEventDispatcher();

$eventDispatcher->addListener('keyboard.c', function($event) use ($logWidget) {
    // Clear logs
    $logWidget->setLogs([]);
});

$eventDispatcher->addListener('keyboard.r', function($event) use ($metricsWidget) {
    // Refresh metrics
    $metricsWidget->setMetrics([
        new SystemMetric("CPU", rand(20, 80), 100.0, "%", "blue"),
        new SystemMetric("Memory", rand(2, 6), 8.0, "GB", "green"),
        new SystemMetric("Disk", rand(50, 200), 500.0, "MB/s", "yellow"),
        new SystemMetric("Network", rand(10, 50), 100.0, "Mbps", "cyan")
    ]);
});

$eventDispatcher->addListener('keyboard.space', function($event) use ($app) {
    // Toggle pause
    static $paused = false;
    $paused = !$paused;
    if ($paused) {
        $app->setFPS(1);
    } else {
        $app->setFPS(15);
    }
});

// Setup update callback
$app->setUpdateCallback(function($app) use ($logWidget, $metricsWidget) {
    static $counter = 0;
    $counter++;
    
    // Add random log entry occasionally
    if ($counter % 30 === 0) {
        $levels = [LogLevel::DEBUG, LogLevel::INFO, LogLevel::WARNING];
        $level = $levels[array_rand($levels)];
        $messages = [
            "Processing user request",
            "Cache hit for key",
            "Database query executed",
            "API call completed",
            "Background task finished"
        ];
        $message = $messages[array_rand($messages)];
        
        $logWidget->addLog($level, $message, "system");
    }
    
    // Update metrics occasionally
    if ($counter % 20 === 0) {
        $cpu = rand(20, 80);
        $memory = rand(2, 6);
        $disk = rand(50, 200);
        $network = rand(10, 50);
        
        $metricsWidget->setMetrics([
            new SystemMetric("CPU", $cpu, 100.0, "%", "blue"),
            new SystemMetric("Memory", $memory, 8.0, "GB", "green"),
            new SystemMetric("Disk I/O", $disk, 500.0, "MB/s", "yellow"),
            new SystemMetric("Network", $network, 100.0, "Mbps", "cyan")
        ]);
    }
});

// Add initial content
$logWidget->addLog(LogLevel::INFO, "Interactive dashboard started", "system");
$logWidget->addLog(LogLevel::INFO, "Press 'q' to quit, 'r' to refresh, 'c' to clear logs", "help");

// Run application
$app->run();
```

**[NEW FILE] examples/etl-monitor.php**
```php
<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use PhpTuiDashboard\Application;
use PhpTuiDashboard\Position;
use PhpTuiDashboard\Size;
use PhpTuiDashboard\Layout\FlexLayout;
use PhpTuiDashboard\Layout\FlexDirection;
use PhpTuiDashboard\Widget\LogWidget;
use PhpTuiDashboard\Widget\ProgressBar;
use PhpTuiDashboard\Widget\SystemMetricsWidget;
use PhpTuiDashboard\Widget\FooterBar;
use PhpTuiDashboard\Widget\LogLevel;
use PhpTuiDashboard\Widget\SystemMetric;

class ETLMonitor
{
    private Application $app;
    private LogWidget $logWidget;
    private SystemMetricsWidget $metricsWidget;
    private array $progressBars = [];
    private FooterBar $footerBar;
    private array $etlStatus = [
        'import' => ['progress' => 0.0, 'records' => 0, 'errors' => 0],
        'transform' => ['progress' => 0.0, 'records' => 0, 'errors' => 0],
        'export' => ['progress' => 0.0, 'records' => 0, 'errors' => 0]
    ];

    public function __construct()
    {
        $this->app = new Application(80, 24);
        $this->app->setFPS(10);
        $this->setupComponents();
        $this->setupEventHandlers();
        $this->setupUpdateCallback();
    }

    private function setupComponents(): void
    {
        // Main layout
        $mainLayout = new FlexLayout(FlexDirection::COLUMN, 1, 0);
        
        // Progress bars
        $this->progressBars['import'] = new ProgressBar(
            new Position(0, 0), new Size(78, 3), "Import Stage"
        );
        $this->progressBars['transform'] = new ProgressBar(
            new Position(0, 0), new Size(78, 3), "Transform Stage"
        );
        $this->progressBars['export'] = new ProgressBar(
            new Position(0, 0), new Size(78, 3), "Export Stage"
        );

        // Content layout (metrics and logs side by side)
        $contentLayout = new FlexLayout(FlexDirection::ROW, 1, 0);
        
        $this->metricsWidget = new SystemMetricsWidget(
            new Position(0, 0), new Size(40, 10), "System Metrics"
        );
        
        $this->logWidget = new LogWidget(
            new Position(0, 0), new Size(40, 10), "ETL Process Log"
        );

        // Footer
        $this->footerBar = new FooterBar(new Position(0, 0), new Size(80, 1));
        $this->footerBar->addTextSection("F1:Start", ['bold' => true]);
        $this->footerBar->addTextSection("F2:Pause", ['bold' => true]);
        $this->footerBar->addTextSection("F3:Reset", ['bold' => true]);
        $this->footerBar->addTextSection("l:Logs", ['bold' => true]);
        $this->footerBar->addTextSection("q:Quit", ['color' => 'red']);
        $this->footerBar->addTextSection("Status: Ready", ['color' => 'green'], 15);

        // Build layout
        $contentLayout->addComponent($this->metricsWidget, 1);
        $contentLayout->addComponent($this->logWidget, 1);
        
        $mainLayout->addComponent($this->progressBars['import'], 3);
        $mainLayout->addComponent($contentLayout, 1);
        $mainLayout->addComponent($this->progressBars['transform'], 3);
        $mainLayout->addComponent($this->progressBars['export'], 3);
        $mainLayout->addComponent($this->footerBar, 1);

        // Add to application
        $this->app->addComponent($mainLayout);
    }

    private function setupEventHandlers(): void
    {
        $eventDispatcher = $this->app->getEventDispatcher();

        $eventDispatcher->addListener('keyboard.q', function($event) {
            $this->logWidget->addLog(LogLevel::INFO, "Shutting down ETL monitor", "system");
            $this->app->stop();
        });

        $eventDispatcher->addListener('keyboard.l', function($event) {
            $this->logWidget->scrollUp(5);
        });

        $eventDispatcher->addListener('keyboard.down', function($event) {
            $this->logWidget->scrollDown(5);
        });

        $eventDispatcher->addListener('keyboard.up', function($event) {
            $this->logWidget->scrollUp(5);
        });

        // Simulate F1-F3 keys (since we can't easily detect function keys in this simple implementation)
        $eventDispatcher->addListener('keyboard.1', function($event) {
            $this->startETLProcess();
        });

        $eventDispatcher->addListener('keyboard.2', function($event) {
            $this->pauseETLProcess();
        });

        $eventDispatcher->addListener('keyboard.3', function($event) {
            $this->resetETLProcess();
        });
    }

    private function setupUpdateCallback(): void
    {
        $this->app->setUpdateCallback(function($app) {
            static $counter = 0;
            $counter++;

            // Simulate ETL progress
            if ($this->etlStatus['import']['progress'] < 1.0) {
                $this->etlStatus['import']['progress'] += 0.02;
                $this->etlStatus['import']['records'] += rand(10, 50);
                if (rand(1, 100) === 1) {
                    $this->etlStatus['import']['errors']++;
                    $this->logWidget->addLog(LogLevel::ERROR, "Import error: Invalid record format", "import");
                }
            } elseif ($this->etlStatus['transform']['progress'] < 1.0) {
                $this->etlStatus['transform']['progress'] += 0.03;
                $this->etlStatus['transform']['records'] += rand(8, 30);
            } elseif ($this->etlStatus['export']['progress'] < 1.0) {
                $this->etlStatus['export']['progress'] += 0.04;
                $this->etlStatus['export']['records'] += rand(5, 25);
            }

            // Update progress bars
            foreach ($this->progressBars as $stage => $progressBar) {
                $progressBar->setProgress($this->etlStatus[$stage]['progress']);
                $records = $this->etlStatus[$stage]['records'];
                $errors = $this->etlStatus[$stage]['errors'];
                $label = sprintf("%d records", $records);
                if ($errors > 0) {
                    $label .= sprintf(" (%d errors)", $errors);
                }
                $progressBar->setLabel($label);
            }

            // Update metrics
            if ($counter % 15 === 0) {
                $cpu = 30 + $this->etlStatus['import']['progress'] * 40 + rand(-10, 10);
                $memory = 2.5 + $this->etlStatus['transform']['progress'] * 3 + rand(-1, 1);
                $disk = 50 + $this->etlStatus['export']['progress'] * 100 + rand(-20, 20);
                $network = 10 + rand(-5, 15);

                $this->metricsWidget->setMetrics([
                    new SystemMetric("CPU Usage", $cpu, 100.0, "%", "blue"),
                    new SystemMetric("Memory", $memory, 8.0, "GB", "green"),
                    new SystemMetric("Disk I/O", $disk, 500.0, "MB/s", "yellow"),
                    new SystemMetric("Network", $network, 100.0, "Mbps", "cyan")
                ]);
            }

            // Add progress logs
            if ($counter % 25 === 0) {
                $totalProgress = ($this->etlStatus['import']['progress'] + 
                                $this->etlStatus['transform']['progress'] + 
                                $this->etlStatus['export']['progress']) / 3;
                
                $this->logWidget->addLog(
                    LogLevel::INFO, 
                    sprintf("Overall progress: %d%%", (int)($totalProgress * 100)),
                    "etl"
                );
            }
        });
    }

    private function startETLProcess(): void
    {
        $this->logWidget->addLog(LogLevel::INFO, "Starting ETL process", "system");
        // Reset and start would be implemented here
    }

    private function pauseETLProcess(): void
    {
        $this->logWidget->addLog(LogLevel::WARNING, "ETL process paused", "system");
        // Pause logic would be implemented here
    }

    private function resetETLProcess(): void
    {
        $this->logWidget->addLog(LogLevel::INFO, "Resetting ETL process", "system");
        
        foreach ($this->etlStatus as $stage => &$status) {
            $status['progress'] = 0.0;
            $status['records'] = 0;
            $status['errors'] = 0;
        }
    }

    public function run(): void
    {
        $this->logWidget->addLog(LogLevel::INFO, "ETL Monitor initialized", "system");
        $this->logWidget->addLog(LogLevel::INFO, "Press '1' to start, '2' to pause, '3' to reset", "help");
        $this->logWidget->addLog(LogLevel::INFO, "Use arrow keys to scroll logs", "help");
        
        $this->app->run();
    }
}

// Run the ETL monitor
$monitor = new ETLMonitor();
$monitor->run();
```

### Verification Steps
1. Run `php examples/interactive.php` to test interactive elements
2. Execute `php examples/etl-monitor.php` to verify complete ETL monitoring application
3. Test keyboard input handling (q, r, c, space, arrow keys)
4. Verify real-time updates and performance
5. Test event system responsiveness

## Phase 5: Documentation and Open Source Preparation

### Objective
Create comprehensive documentation, examples, and prepare the library for open source release.

### Tasks
1. **Documentation**
   - Create comprehensive README.md
   - Write API documentation
   - Create getting started guide
   - Add examples and tutorials

2. **Code Quality**
   - Add comprehensive test suite
   - Implement error handling
   - Add code documentation blocks
   - Ensure coding standards compliance

3. **Open Source Setup**
   - Create LICENSE file
   - Add CONTRIBUTING.md
   - Setup changelog
   - Create release documentation

4. **Examples and Demos**
   - Create diverse example applications
   - Add performance benchmarks
   - Create tutorial walkthroughs

### Deliverables
- [NEW FILE] `README.md` - Main project documentation
- [NEW FILE] `docs/getting-started.md` - Getting started guide
- [NEW FILE] `docs/api-reference.md` - API documentation
- [NEW FILE] `docs/examples.md` - Examples documentation
- [NEW FILE] `LICENSE` - MIT license
- [NEW FILE] `CONTRIBUTING.md` - Contribution guidelines
- [NEW FILE] `CHANGELOG.md` - Version history
- [NEW FILE] `tests/` directory with comprehensive tests
- [NEW FILE] `examples/gallery/` with diverse examples
- [MODIFY] All source files with proper PHPDoc comments

### Source Code

**[NEW FILE] README.md**
```markdown
# PHP TUI Dashboard Framework

A lightweight, dependency-minimal PHP framework for building beautiful terminal-based dashboards and TUI applications.

## Features

- 🎨 **Widget-based Architecture** - Reusable components for common dashboard elements
- 📐 **Flexible Layout System** - Flex and Grid layouts for responsive design
- 🚀 **High Performance** - Differential rendering and optimized updates
- ⌨️ **Interactive** - Full keyboard input and event handling
- 📊 **Built-in Widgets** - Log viewers, progress bars, system metrics, and more
- 🎯 **ETL Focused** - Designed specifically for monitoring data pipelines
- 📦 **Minimal Dependencies** - Only requires Solo Screen for terminal rendering

## Quick Start

### Installation

```bash
composer require your-org/php-tui-dashboard
```

### Basic Example

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use PhpTuiDashboard\Application;
use PhpTuiDashboard\Widget\SystemMetricsWidget;
use PhpTuiDashboard\Widget\LogWidget;
use PhpTuiDashboard\Widget\LogLevel;

$app = new Application(80, 24);

// Add widgets
$metrics = new SystemMetricsWidget(new Position(0, 0), new Size(40, 10), "System");
$logs = new LogWidget(new Position(0, 0), new Size(40, 10), "Logs");

$app->addComponent($metrics);
$app->addComponent($logs);

// Add initial content
$logs->addLog(LogLevel::INFO, "Dashboard started!");

// Run the application
$app->run();
```

## Core Concepts

### Components

Everything in the framework is built from `Component` objects that can be positioned, sized, and rendered.

```php
use PhpTuiDashboard\Component;
use PhpTuiDashboard\Position;
use PhpTuiDashboard\Size;

class MyComponent extends Component
{
    public function render(Renderer $renderer): void
    {
        $renderer->moveTo($this->position);
        $renderer->write("Hello, Terminal!");
    }
}
```

### Layouts

Use flex and grid layouts to arrange components:

```php
use PhpTuiDashboard\Layout\FlexLayout;
use PhpTuiDashboard\Layout\FlexDirection;

$layout = new FlexLayout(FlexDirection::ROW, 1, 1);
$layout->addComponent($component1, 1); // 1 part
$layout->addComponent($component2, 2); // 2 parts
```

### Widgets

Built-in widgets for common dashboard needs:

- **LogWidget** - Scrolling log viewer with filtering
- **ProgressBar** - Horizontal progress bars with segments
- **SystemMetricsWidget** - Gauges for system metrics
- **FooterBar** - Status bar with sections

## Examples

### ETL Dashboard

```php
use PhpTuiDashboard\Widget\ProgressBar;
use PhpTuiDashboard\Widget\SystemMetric;

$progress = new ProgressBar(new Position(0, 0), new Size(50, 3), "Import");
$progress->setProgress(0.75);

$metrics = new SystemMetricsWidget(new Position(0, 0), new Size(50, 8));
$metrics->addMetric(new SystemMetric("CPU", 65.5, 100.0, "%"));
$metrics->addMetric(new SystemMetric("Memory", 4.2, 8.0, "GB"));
```

### Interactive Application

```php
$app = new Application(80, 24);

// Handle keyboard events
$app->getEventDispatcher()->addListener('keyboard.q', function($event) {
    $app->stop();
});

// Set up real-time updates
$app->setUpdateCallback(function($app) {
    // Update data periodically
});

$app->run();
```

## Documentation

- [Getting Started](docs/getting-started.md)
- [API Reference](docs/api-reference.md)
- [Examples Gallery](docs/examples.md)

## Requirements

- PHP 8.1 or higher
- mbstring extension
- Solo Screen library

## Performance

The framework is optimized for real-time dashboard applications:

- **Differential Rendering** - Only redraw changed content
- **Efficient Layout** - Minimal layout recalculations
- **Smart Updates** - Configurable update frequencies
- **Memory Efficient** - Minimal object allocation

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Inspired By

This library is inspired by excellent TUI frameworks like:
- [Tview](https://github.com/rivo/tview) for Go
- [Bubbletea](https://github.com/charmbracelet/bubbletea) for Go
- [Ratatui](https://github.com/ratatui-org/ratatui) for Rust
```

**[NEW FILE] docs/getting-started.md**
```markdown
# Getting Started Guide

## Installation

### Requirements

- PHP 8.1+
- mbstring extension
- Unix-like terminal (Linux, macOS, WSL)

### Composer Installation

```bash
composer require your-org/php-tui-dashboard
```

### Manual Installation

Download the source and include the autoloader:

```php
require __DIR__ . '/path/to/vendor/autoload.php';
```

## Your First Dashboard

### Step 1: Create the Application

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use PhpTuiDashboard\Application;

// Create an 80x24 terminal application
$app = new Application(80, 24);
```

### Step 2: Add Components

```php
use PhpTuiDashboard\Widget\SystemMetricsWidget;
use PhpTuiDashboard\Position;
use PhpTuiDashboard\Size;

// Create a system metrics widget
$metrics = new SystemMetricsWidget(
    new Position(5, 5),    // x, y position
    new Size(30, 10),      // width, height
    "System Resources"     // title
);

// Add it to the application
$app->addComponent($metrics);
```

### Step 3: Run the Application

```php
$app->run();
```

Save this as `dashboard.php` and run:

```bash
php dashboard.php
```

## Understanding the Architecture

### Components

Components are the building blocks of your dashboard:

```php
use PhpTuiDashboard\Component;
use PhpTuiDashboard\Renderer;

class CustomWidget extends Component
{
    public function render(Renderer $renderer): void
    {
        // Get the inner area (respecting borders)
        ['position' => $pos, 'size' => $size] = $this->getInnerArea();
        
        // Render content
        $renderer->moveTo($pos);
        $renderer->write("Custom content");
    }
}
```

### Layouts

Layouts automatically position and size components:

#### Flex Layout

```php
use PhpTuiDashboard\Layout\FlexLayout;
use PhpTuiDashboard\Layout\FlexDirection;

// Vertical flex layout
$layout = new FlexLayout(FlexDirection::COLUMN, 1, 1);

// Add components with size constraints
$layout->addComponent($header, 3);    // Fixed 3 rows
$layout->addComponent($content, 1);   // Flexible (1 part)
$layout->addComponent($footer, 2);   // Fixed 2 rows
```

#### Grid Layout

```php
use PhpTuiDashboard\Layout\GridLayout;

// 2x2 grid
$grid = new GridLayout(2, 2, 1);

$grid->addComponent($widget1); // Auto-placed
$grid->addComponent($widget2); // Auto-placed
```

### Events

Handle user input and system events:

```php
$eventDispatcher = $app->getEventDispatcher();

// Handle specific key
$eventDispatcher->addListener('keyboard.q', function($event) {
    $app->stop();
});

// Handle all keyboard events
$eventDispatcher->addListener('keyboard.*', function($event) {
    echo "Key pressed: " . $event->getKey();
});
```

## Building an ETL Dashboard

### Step 1: Define the Layout

```php
use PhpTuiDashboard\Layout\FlexLayout;
use PhpTuiDashboard\Layout\FlexDirection;

$mainLayout = new FlexLayout(FlexDirection::COLUMN, 1, 0);

// Progress bars at top
$mainLayout->addComponent($importProgress, 3);
$mainLayout->addComponent($transformProgress, 3);

// Content area (metrics + logs)
$contentLayout = new FlexLayout(FlexDirection::ROW, 1, 1);
$contentLayout->addComponent($metricsWidget, 1);
$contentLayout->addComponent($logWidget, 1);
$mainLayout->addComponent($contentLayout, 1);

// Footer
$mainLayout->addComponent($footerBar, 1);
```

### Step 2: Create Widgets

```php
use PhpTuiDashboard\Widget\ProgressBar;
use PhpTuiDashboard\Widget\SystemMetricsWidget;
use PhpTuiDashboard\Widget\LogWidget;
use PhpTuiDashboard\Widget\LogLevel;
use PhpTuiDashboard\Widget\SystemMetric;

// Progress bars
$importProgress = new ProgressBar(new Position(0, 0), new Size(50, 3), "Import");
$transformProgress = new ProgressBar(new Position(0, 0), new Size(50, 3), "Transform");

// Metrics
$metrics = new SystemMetricsWidget(new Position(0, 0), new Size(40, 12));
$metrics->addMetric(new SystemMetric("CPU", 45.2, 100.0, "%", "blue"));
$metrics->addMetric(new SystemMetric("Memory", 3.8, 8.0, "GB", "green"));

// Logs
$logs = new LogWidget(new Position(0, 0), new Size(40, 12));
$logs->addLog(LogLevel::INFO, "ETL process started");
```

### Step 3: Add Real-time Updates

```php
$app->setUpdateCallback(function($app) use ($importProgress, $metrics, $logs) {
    static $progress = 0.0;
    
    // Update progress
    $progress += 0.01;
    $importProgress->setProgress($progress);
    
    // Update metrics
    if ($progress % 0.1 < 0.01) {
        $cpu = rand(30, 70);
        $metrics->setMetrics([
            new SystemMetric("CPU", $cpu, 100.0, "%", "blue")
        ]);
        
        $logs->addLog(LogLevel::INFO, sprintf("Progress: %d%%", (int)($progress * 100)));
    }
    
    // Stop when complete
    if ($progress >= 1.0) {
        $app->stop();
    }
});
```

## Best Practices

### Performance

1. **Use Differential Rendering** - The framework automatically handles this
2. **Limit Update Frequency** - Set appropriate FPS for your use case
3. **Optimize Layout Calculations** - Cache complex layouts

### User Experience

1. **Provide Keyboard Shortcuts** - Always include a quit key ('q')
2. **Show Status Information** - Use footer bars for help text
3. **Handle Terminal Resize** - The framework handles this automatically

### Code Organization

1. **Separate Components** - Create reusable widget classes
2. **Use Dependency Injection** - Pass data providers to widgets
3. **Implement Interfaces** - Follow the component and event interfaces

## Troubleshooting

### Common Issues

**Terminal shows garbled text**
- Ensure your terminal supports ANSI escape codes
- Try a different terminal emulator

**Keyboard input not working**
- Check that raw mode is enabled (handled automatically)
- Verify you're on a Unix-like system

**Performance issues**
- Reduce FPS setting
- Optimize update callbacks
- Check for memory leaks

### Debug Mode

Enable debug output:

```php
$app->setFPS(1); // Slow down for debugging
```

## Next Steps

- Read the [API Reference](api-reference.md)
- Browse the [Examples Gallery](examples.md)
- Learn about [Advanced Topics](advanced.md)
```

### Verification Steps
1. Verify all documentation files are created and properly formatted
2. Test that all examples run without errors
3. Confirm README provides clear installation and usage instructions
4. Check that API documentation covers all public methods
5. Validate that contribution guidelines are complete

## Phase 6: Testing and Quality Assurance

### Objective
Ensure the library is robust, well-tested, and production-ready through comprehensive testing and quality assurance.

### Tasks
1. **Unit Tests**
   - Test all component classes
   - Verify layout calculations
   - Test widget functionality
   - Cover edge cases and error conditions

2. **Integration Tests**
   - Test component interactions
   - Verify event system functionality
   - Test rendering pipeline
   - Validate performance characteristics

3. **Performance Tests**
   - Benchmark rendering performance
   - Test memory usage patterns
   - Verify differential rendering efficiency
   - Profile with large datasets

4. **Quality Assurance**
   - Code style compliance
   - Documentation coverage
   - Security analysis
   - Compatibility testing

### Deliverables
- [NEW FILE] `tests/Unit/ComponentTest.php` - Component unit tests
- [NEW FILE] `tests/Unit/LayoutTest.php` - Layout system tests
- [NEW FILE] `tests/Unit/WidgetTest.php` - Widget tests
- [NEW FILE] `tests/Integration/ApplicationTest.php` - Integration tests
- [NEW FILE] `tests/Performance/RenderingTest.php` - Performance tests
- [NEW FILE] `phpunit.xml` - Test configuration
- [NEW FILE] `scripts/test.sh` - Test runner script
- [NEW FILE] `scripts/benchmark.php` - Performance benchmark

### Verification Steps
1. Run complete test suite with `./scripts/test.sh`
2. Verify all tests pass (100% coverage goal)
3. Execute performance benchmarks and validate results
4. Test on multiple PHP versions (8.1, 8.2, 8.3)
5. Validate memory usage stays within acceptable limits

## Final Deliverables

### Implementation Report

The final phase will generate a comprehensive implementation report documenting:

1. **Summary** - Overview of completed framework
2. **Files Created** - Complete list of all new files
3. **Key Changes** - Main technical achievements
4. **Technical Decisions** - Important design choices
5. **Testing Notes** - Verification procedures
6. **Usage Examples** - Practical implementation examples
7. **Documentation Updates** - Documentation improvements
8. **Next Steps** - Future enhancement opportunities

The report will be saved to:
`_ai/backlog/reports/260311_1250__IMPLEMENTATION_REPORT__php-tui-dashboard-framework.md`

### Success Criteria

- ✅ Complete PHP TUI framework with minimal dependencies
- ✅ Flexible layout system (Flex + Grid)
- ✅ Essential widgets (logs, progress, metrics, footer)
- ✅ Interactive event handling and keyboard input
- ✅ Real-time update capabilities
- ✅ Comprehensive documentation and examples
- ✅ Production-ready testing and quality assurance
- ✅ Open source ready with proper licensing

This implementation plan provides a complete roadmap for creating a lightweight, powerful PHP TUI dashboard framework specifically designed for ETL monitoring applications, with minimal dependencies and maximum flexibility.
