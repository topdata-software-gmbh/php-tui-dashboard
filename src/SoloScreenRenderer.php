<?php declare(strict_types=1);

namespace PhpTuiDashboard;

use SoloTerm\Screen\Screen;

class SoloScreenRenderer implements Renderer
{
    private Screen $screen;
    private ?string $currentStyle = null;
    private int $lastRenderSeqNo = 0;

    public function __construct(int $width, int $height)
    {
        $this->screen = new Screen($width, $height);
    }

    public function clear(): void
    {
        // Clear screen and reset cursor position using Screen's API
        $this->screen->write("\e[2J");
        $this->screen->moveCursorRow(0);
        $this->screen->moveCursorCol(0);
    }

    public function write(string $text): void
    {
        // Let Screen handle ANSI parsing and cursor management
        if ($this->currentStyle) {
            $this->screen->write($this->currentStyle);
            $this->screen->write($text);
            $this->screen->write("\e[0m");
        } else {
            $this->screen->write($text);
        }
    }

    public function moveTo(Position $position): void
    {
        // Use Screen's built-in cursor positioning
        $this->screen->moveCursorRow($position->y);
        $this->screen->moveCursorCol($position->x);
    }

    public function setStyle(?string $style): void
    {
        // Store style for next write operation
        $this->currentStyle = $style;
    }

    public function getOutput(?int $sequenceNumber = null): string
    {
        if ($sequenceNumber !== null) {
            // Differential rendering - only output changes
            $output = $this->screen->output($sequenceNumber);
            $this->lastRenderSeqNo = $this->screen->getLastRenderedSeqNo();
            return $output;
        }
        
        // Full rendering
        $output = $this->screen->output();
        $this->lastRenderSeqNo = $this->screen->getLastRenderedSeqNo();
        return $output;
    }

    public function getLastRenderedSeqNo(): ?int
    {
        return $this->lastRenderSeqNo;
    }

    public function getSize(): Size
    {
        // Return Screen's dimensions (set at construction)
        // For terminal resize detection, use external polling (e.g., SIGWINCH)
        return new Size($this->screen->width, $this->screen->height);
    }
}
