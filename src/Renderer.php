<?php declare(strict_types=1);

namespace PhpTuiDashboard;

interface Renderer
{
    public function clear(): void;
    
    public function write(string $text): void;
    
    public function moveTo(Position $position): void;
    
    public function setStyle(?string $style): void;
    
    public function getOutput(?int $sequenceNumber = null): string;
    
    public function getLastRenderedSeqNo(): ?int;
    
    public function getSize(): Size;
}
