<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use PhpTuiDashboard\SoloScreenRenderer;
use PhpTuiDashboard\Position;

// Basic rendering demonstration
$renderer = new SoloScreenRenderer(80, 24);

$renderer->clear();
$renderer->moveTo(new Position(1, 1));
$renderer->setStyle("\e[1;34m");
$renderer->write("PHP TUI Dashboard Framework");
$renderer->setStyle(null);

$renderer->moveTo(new Position(1, 3));
$renderer->write("Basic rendering test with Solo Screen");

echo $renderer->getOutput();
