<?php declare(strict_types=1);

namespace PhpTuiDashboard\Input;

use PhpTuiDashboard\Event\EventDispatcher;
use PhpTuiDashboard\Event\KeyboardEvent;

class InputHandler
{
    private bool $reading = false;

    public function processInput(EventDispatcher $eventDispatcher): void
    {
        // Non-blocking input check
        $read = [STDIN];
        $write = [];
        $except = [];
        
        if (stream_select($read, $write, $except, 0, 0) > 0) {
            $char = fread(STDIN, 1);
            
            if ($char === false || $char === '') {
                return;
            }
            
            $this->handleCharacter($char, $eventDispatcher);
        }
    }

    private function handleCharacter(string $char, EventDispatcher $eventDispatcher): void
    {
        // Handle special keys and combinations
        switch ($char) {
            case "\03": // ESC or start of escape sequence
                $this->handleEscapeSequence($eventDispatcher);
                break;
                
            case "\t":
                $eventDispatcher->dispatch(new KeyboardEvent('tab'));
                break;
                
            case "\n":
            case "\r":
                $eventDispatcher->dispatch(new KeyboardEvent('enter'));
                break;
                
            case "\x7F": // Backspace
                $eventDispatcher->dispatch(new KeyboardEvent('backspace'));
                break;
                
            case "\x04": // Ctrl+D
                $eventDispatcher->dispatch(new KeyboardEvent('ctrl_d'));
                break;
                
            case "\x03": // Ctrl+C
                $eventDispatcher->dispatch(new KeyboardEvent('ctrl_c'));
                break;
                
            default:
                // Regular printable characters
                if (ord($char) >= 32 && ord($char) <= 126) {
                    $eventDispatcher->dispatch(new KeyboardEvent($char));
                }
                break;
        }
    }

    private function handleEscapeSequence(EventDispatcher $eventDispatcher): void
    {
        // Read additional characters to determine the escape sequence
        $seq = '';
        $timeout = microtime(true) + 0.1; // 100ms timeout
        
        while (microtime(true) < $timeout) {
            $read = [STDIN];
            if (stream_select($read, $write, $except, 0, 10000) > 0) {
                $char = fread(STDIN, 1);
                if ($char === false || $char === '') {
                    break;
                }
                $seq .= $char;
                
                // Check for common escape sequences
                if (str_starts_with($seq, '[')) {
                    if (preg_match('/^\[([ABCDEFHP])$/', $seq, $matches)) {
                        $this->handleArrowKey($matches[1], $eventDispatcher);
                        return;
                    }
                }
                
                if (strlen($seq) >= 4) {
                    break; // Too long, probably not a valid sequence
                }
            }
        }
        
        // If we couldn't identify a specific sequence, treat as ESC
        $eventDispatcher->dispatch(new KeyboardEvent('escape'));
    }

    private function handleArrowKey(string $key, EventDispatcher $eventDispatcher): void
    {
        $arrowKeys = [
            'A' => 'up',
            'B' => 'down', 
            'C' => 'right',
            'D' => 'left',
            'H' => 'home',
            'F' => 'end'
        ];
        
        if (isset($arrowKeys[$key])) {
            $eventDispatcher->dispatch(new KeyboardEvent($arrowKeys[$key]));
        }
    }
}
