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
composer require topdata-software-gmbh/php-tui-dashboard
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
