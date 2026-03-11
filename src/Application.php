<?php declare(strict_types=1);

namespace PhpTuiDashboard;

use PhpTuiDashboard\Event\EventDispatcher;
use PhpTuiDashboard\Event\TimerEvent;
use PhpTuiDashboard\Event\Event;
use PhpTuiDashboard\Input\InputHandler;
use PhpTuiDashboard\Layout\FlexLayout;
use PhpTuiDashboard\Layout\FlexDirection;

class Application
{
    private SoloScreenRenderer $renderer;
    private EventDispatcher $eventDispatcher;
    private InputHandler $inputHandler;
    private FlexLayout $layout;
    private array $components = [];
    private bool $running = false;
    private int $fps = 10;
    private ?\Closure $updateCallback = null;
    private int $lastRenderSeqNo = 0;

    public function __construct(int $width = 80, int $height = 24)
    {
        $this->renderer = new SoloScreenRenderer($width, $height);
        $this->eventDispatcher = new EventDispatcher();
        $this->inputHandler = new InputHandler();
        $this->layout = new FlexLayout(FlexDirection::COLUMN, 1, 0);
        
        $this->setupEventHandlers();
    }

    public function addComponent(Component $component, mixed $constraint = null): void
    {
        $this->components[] = $component;
        $this->layout->addComponent($component, $constraint);
    }

    public function setUpdateCallback(\Closure $callback): void
    {
        $this->updateCallback = $callback;
    }

    public function setFPS(int $fps): void
    {
        $this->fps = max(1, $fps);
    }

    public function run(): void
    {
        $this->running = true;
        $this->enableRawMode();
        
        $lastRender = 0;
        $frameInterval = 1000000 / $this->fps; // microseconds
        
        while ($this->running) {
            $startTime = microtime(true);
            
            // Handle input
            $this->inputHandler->processInput($this->eventDispatcher);
            
            // Process events
            $this->eventDispatcher->dispatchEvents();
            
            // Update data
            if ($this->updateCallback) {
                ($this->updateCallback)($this);
            }
            
            // Render frame
            $currentTime = (int) (microtime(true) * 1000000);
            if ($currentTime - $lastRender >= $frameInterval) {
                $this->render();
                $lastRender = $currentTime;
            }
            
            // Sleep to maintain FPS
            $elapsed = (microtime(true) - $startTime) * 1000000;
            if ($elapsed < $frameInterval) {
                usleep((int) ($frameInterval - $elapsed));
            }
        }
        
        $this->disableRawMode();
    }

    public function stop(): void
    {
        $this->running = false;
    }

    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }

    public function getRenderer(): SoloScreenRenderer
    {
        return $this->renderer;
    }

    private function render(): void
    {
        // Clear only what's necessary for differential rendering
        if ($this->lastRenderSeqNo === 0) {
            $this->renderer->clear();
        }
        
        // Calculate layout
        $container = new Area(
            new Position(0, 0),
            $this->renderer->getSize()
        );
        
        $areas = $this->layout->calculateAreas($container);
        
        // Position and render components
        foreach ($areas as $index => $area) {
            $components = $this->layout->getComponents();
            if (isset($components[$index])) {
                $component = $components[$index];
                $component->setPosition($area->position);
                $component->setSize($area->size);
                $component->render($this->renderer);
            }
        }
        
        // Use differential rendering for performance
        $output = $this->renderer->getOutput($this->lastRenderSeqNo);
        if ($output !== '') {
            echo $output;
        }
        
        // Update sequence number for next frame
        $this->lastRenderSeqNo = $this->renderer->getLastRenderedSeqNo();
    }

    private function setupEventHandlers(): void
    {
        // Handle quit events
        $this->eventDispatcher->addListener('keyboard.q', function($event) {
            $this->stop();
        });
        
        $this->eventDispatcher->addListener('keyboard.ctrl_c', function($event) {
            $this->stop();
        });
        
        // Handle window resize
        $this->eventDispatcher->addListener('system.resize', function($event) {
            $this->handleResize($event->getData()['width'], $event->getData()['height']);
        });
    }

    private function handleResize(int $width, int $height): void
    {
        // Screen does not auto-resize - must recreate with new dimensions
        // Buffer contents are lost on resize (full re-render required)
        $this->renderer = new SoloScreenRenderer($width, $height);
        $this->lastRenderSeqNo = 0; // Reset for full re-render
        
        // Trigger layout recalculation
        $this->eventDispatcher->dispatch(new class($width, $height) implements Event {
            public function __construct(private int $width, private int $height) {}
            public function getType(): string { return 'system.resized'; }
            public function getData(): array { return ['width' => $this->width, 'height' => $this->height]; }
            public function getTimestamp(): float { return microtime(true); }
        });
    }

    private function enableRawMode(): void
    {
        // Enable raw terminal mode for input handling
        system('stty -icanon -echo');
    }

    private function disableRawMode(): void
    {
        // Restore normal terminal mode
        system('stty icanon echo');
    }
}
