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
