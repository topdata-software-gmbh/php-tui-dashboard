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
