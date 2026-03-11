<?php declare(strict_types=1);

namespace PhpTuiDashboard\Widget;

use PhpTuiDashboard\Component;
use PhpTuiDashboard\Renderer;
use PhpTuiDashboard\Position;
use PhpTuiDashboard\Size;

abstract class Widget extends Component
{
    protected string $title = '';
    protected bool $border = true;
    protected array $titleStyle = ['bold' => true, 'color' => 'blue'];
    protected array $borderStyle = ['color' => 'white'];

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function hasBorder(): bool
    {
        return $this->border;
    }

    public function setBorder(bool $border): void
    {
        $this->border = $border;
    }

    protected function renderBorder(Renderer $renderer): void
    {
        if (!$this->border || $this->size->height < 3 || $this->size->width < 3) {
            return;
        }

        $borderStyle = $this->getStyleString($this->borderStyle);
        $renderer->setStyle($borderStyle);

        // Top border with title
        $topBorder = "┌";
        if ($this->title) {
            $titlePadding = max(0, ($this->size->width - strlen($this->title) - 4) / 2);
            $titleLeft = (int) floor($titlePadding);
            $titleRight = (int) ceil($titlePadding);
            $topBorder .= str_repeat("─", $titleLeft) . " " . $this->title . " " . str_repeat("─", $titleRight);
        } else {
            $topBorder .= str_repeat("─", $this->size->width - 2);
        }
        $topBorder .= "┐";

        $renderer->moveTo($this->position);
        $renderer->write($topBorder);

        // Side borders
        for ($y = 1; $y < $this->size->height - 1; $y++) {
            $renderer->moveTo(new Position($this->position->x, $this->position->y + $y));
            $renderer->write("│");
            $renderer->moveTo(new Position($this->position->x + $this->size->width - 1, $this->position->y + $y));
            $renderer->write("│");
        }

        // Bottom border
        $renderer->moveTo(new Position($this->position->x, $this->position->y + $this->size->height - 1));
        $renderer->write("└" . str_repeat("─", $this->size->width - 2) . "┘");

        $renderer->setStyle(null);
    }

    protected function getInnerArea(): array
    {
        $innerX = $this->position->x + ($this->border ? 1 : 0);
        $innerY = $this->position->y + ($this->border ? 1 : 0);
        $innerWidth = $this->size->width - ($this->border ? 2 : 0);
        $innerHeight = $this->size->height - ($this->border ? 2 : 0);

        return [
            'position' => new Position($innerX, $innerY),
            'size' => new Size($innerWidth, $innerHeight)
        ];
    }

    protected function getStyleString(array $style): string
    {
        // Use Screen's compatible ANSI code generation
        $codes = [];
        
        if ($style['bold'] ?? false) {
            $codes[] = '1';
        }
        
        if ($style['dim'] ?? false) {
            $codes[] = '2';
        }
        
        if ($style['italic'] ?? false) {
            $codes[] = '3';
        }
        
        if ($style['underline'] ?? false) {
            $codes[] = '4';
        }
        
        if (isset($style['color'])) {
            $colors = [
                'black' => '30', 'red' => '31', 'green' => '32', 'yellow' => '33',
                'blue' => '34', 'magenta' => '35', 'cyan' => '36', 'white' => '37',
                'bright_black' => '90', 'bright_red' => '91', 'bright_green' => '92',
                'bright_yellow' => '93', 'bright_blue' => '94', 'bright_magenta' => '95',
                'bright_cyan' => '96', 'bright_white' => '97'
            ];
            $codes[] = $colors[$style['color']] ?? '37';
        }
        
        if (isset($style['bgcolor'])) {
            $colors = [
                'black' => '40', 'red' => '41', 'green' => '42', 'yellow' => '43',
                'blue' => '44', 'magenta' => '45', 'cyan' => '46', 'white' => '47',
                'bright_black' => '100', 'bright_red' => '101', 'bright_green' => '102',
                'bright_yellow' => '103', 'bright_blue' => '104', 'bright_magenta' => '105',
                'bright_cyan' => '106', 'bright_white' => '107'
            ];
            $codes[] = $colors[$style['bgcolor']] ?? '40';
        }
        
        return empty($codes) ? '' : "\e[" . implode(';', $codes) . 'm';
    }

    protected function truncateText(string $text, int $maxLength): string
    {
        if (strlen($text) <= $maxLength) {
            return $text;
        }
        return substr($text, 0, $maxLength - 3) . '...';
    }
}
