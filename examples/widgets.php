<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use PhpTuiDashboard\SoloScreenRenderer;
use PhpTuiDashboard\Position;
use PhpTuiDashboard\Size;
use PhpTuiDashboard\Widget\LogWidget;
use PhpTuiDashboard\Widget\ProgressBar;
use PhpTuiDashboard\Widget\ProgressSegment;
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
