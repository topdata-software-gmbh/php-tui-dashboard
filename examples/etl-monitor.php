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
