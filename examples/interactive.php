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
