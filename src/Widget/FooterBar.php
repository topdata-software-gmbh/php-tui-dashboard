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

    public function __construct(Position $position, Size $size)
    {
        parent::__construct($position, $size);
        $this->setBorder(false); // Footer typically doesn't have border
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
