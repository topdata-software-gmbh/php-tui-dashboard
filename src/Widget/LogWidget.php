<?php declare(strict_types=1);

namespace PhpTuiDashboard\Widget;

use PhpTuiDashboard\Renderer;
use PhpTuiDashboard\Position;
use PhpTuiDashboard\Size;

enum LogLevel
{
    case DEBUG;
    case INFO;
    case WARNING;
    case ERROR;
    case CRITICAL;

    public function getColor(): string
    {
        return match($this) {
            self::DEBUG => 'cyan',
            self::INFO => 'green',
            self::WARNING => 'yellow',
            self::ERROR => 'red',
            self::CRITICAL => 'magenta'
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::DEBUG => '🔍',
            self::INFO => 'ℹ️',
            self::WARNING => '⚠️',
            self::ERROR => '❌',
            self::CRITICAL => '🔥'
        };
    }
}

class LogEntry
{
    public function __construct(
        public readonly LogLevel $level,
        public readonly string $message,
        public readonly \DateTimeImmutable $timestamp,
        public readonly string $source = ''
    ) {}

    public function format(): string
    {
        $time = $this->timestamp->format('H:i:s');
        $source = $this->source ? "[{$this->source}] " : '';
        return "{$time} {$source}{$this->level->getIcon()} {$this->message}";
    }
}

class LogWidget extends Widget
{
    private array $logs = [];
    private int $maxLogs = 100;
    private int $scrollOffset = 0;
    private bool $autoScroll = true;
    private ?LogLevel $filterLevel = null;

    public function __construct(Position $position, Size $size, string $title = 'Logs')
    {
        parent::__construct($position, $size);
        $this->title = $title;
    }

    public function addLog(LogLevel $level, string $message, string $source = ''): void
    {
        $entry = new LogEntry($level, $message, new \DateTimeImmutable(), $source);
        $this->logs[] = $entry;
        
        // Keep only recent logs
        if (count($this->logs) > $this->maxLogs) {
            array_shift($this->logs);
        }
        
        // Auto-scroll to bottom if enabled
        if ($this->autoScroll) {
            $this->scrollOffset = max(0, count($this->logs) - $this->getVisibleLines());
        }
    }

    public function setFilterLevel(?LogLevel $level): void
    {
        $this->filterLevel = $level;
    }

    public function scrollUp(int $lines = 1): void
    {
        $this->scrollOffset = max(0, $this->scrollOffset - $lines);
        $this->autoScroll = false;
    }

    public function scrollDown(int $lines = 1): void
    {
        $maxOffset = max(0, count($this->logs) - $this->getVisibleLines());
        $this->scrollOffset = min($maxOffset, $this->scrollOffset + $lines);
        $this->autoScroll = $this->scrollOffset >= $maxOffset;
    }

    public function render(Renderer $renderer): void
    {
        $this->renderBorder($renderer);
        
        ['position' => $innerPos, 'size' => $innerSize] = $this->getInnerArea();
        
        if ($innerSize->height <= 0 || $innerSize->width <= 0) {
            return;
        }

        // Filter logs
        $filteredLogs = $this->logs;
        if ($this->filterLevel) {
            $filteredLogs = array_filter($filteredLogs, fn($log) => $log->level >= $this->filterLevel);
        }

        // Display visible logs
        $visibleLogs = array_slice($filteredLogs, $this->scrollOffset, $this->getVisibleLines());
        
        foreach ($visibleLogs as $index => $log) {
            $y = $innerPos->y + $index;
            if ($y >= $innerPos->y + $innerSize->height) {
                break;
            }

            $renderer->moveTo(new Position($innerPos->x, $y));
            $renderer->setStyle($this->getStyleString(['color' => $log->level->getColor()]));
            
            $formatted = $this->truncateText($log->format(), $innerSize->width);
            $renderer->write($formatted);
            $renderer->setStyle(null);
        }

        // Show scroll indicator if needed
        if (count($filteredLogs) > $this->getVisibleLines()) {
            $scrollPos = $innerPos->x + $innerSize->width - 1;
            $scrollHeight = $innerSize->height;
            $scrollProgress = $this->scrollOffset / max(1, count($filteredLogs) - $this->getVisibleLines());
            $thumbPos = (int) ($scrollProgress * ($scrollHeight - 1));
            
            for ($y = 0; $y < $scrollHeight; $y++) {
                $renderer->moveTo(new Position($scrollPos, $innerPos->y + $y));
                $char = ($y === $thumbPos) ? '█' : '░';
                $renderer->write($char);
            }
        }
    }

    public function setLogs(array $logs): void
    {
        $this->logs = $logs;
        $this->scrollOffset = 0;
        $this->autoScroll = true;
    }

    private function getVisibleLines(): int
    {
        ['size' => $innerSize] = $this->getInnerArea();
        return max(0, $innerSize->height);
    }
}
