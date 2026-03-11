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
        
        // Create a temporary layout for this render
        $renderLayout = new FlexLayout(FlexDirection::COLUMN, 1, 1);
        
        // Add components to render layout
        $renderLayout->addComponent($this->progressBars['import'], 3);
        $renderLayout->addComponent($this->contentLayout, 1);
        $renderLayout->addComponent($this->progressBars['transform'], 3);
        $renderLayout->addComponent($this->progressBars['export'], 3);
        $renderLayout->addComponent($this->footerBar, 1);

        // Calculate layout areas
        $container = new \PhpTuiDashboard\Area(
            new Position(1, 1), 
            new Size(78, 22)
        );
        
        $areas = $renderLayout->calculateAreas($container);
        
        // Position and render all components
        foreach ($areas as $index => $area) {
            $component = $renderLayout->getComponents()[$index];
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
                // No manual clear needed - differential rendering handles updates
            }
        }
        
        $this->logWidget->addLog(LogLevel::INFO, "ETL process completed successfully!", "system");
        $this->render();
    }
}

// Run the dashboard
$dashboard = new ETLDashboard();
$dashboard->simulateProgress();
