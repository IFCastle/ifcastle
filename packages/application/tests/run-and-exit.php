<?php

declare(strict_types=1);

// Runs TestApplication through Runner::runAndExit() with no engine bound, so the engine fails to
// start. Arguments: the application directory. RunnerTest reads the exit code.

use IfCastle\Application\Bootloader\Builder\BootloaderBuilderInMemory;
use IfCastle\Application\Runner;
use IfCastle\Application\TestApplication;

require __DIR__ . '/../../../vendor/autoload.php';

$appDir                             = $argv[1];

new Runner($appDir, 'test', TestApplication::class)
    ->defineBootloaderBuilder(new BootloaderBuilderInMemory($appDir, 'test', [], [], []))
    ->runAndExit();
