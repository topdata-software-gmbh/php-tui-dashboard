# Getting Started Guide

## Installation

### Requirements

- PHP 8.1+
- mbstring extension
- Unix-like terminal (Linux, macOS, WSL)

### Composer Installation

```bash
composer require topdata-software-gmbh/php-tui-dashboard
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
