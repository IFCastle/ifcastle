<?php

declare(strict_types=1);

namespace IfCastle\Application\Console;

final class NullOutput implements ConsoleOutputInterface
{
    #[\Override]
    public function write(iterable|string $messages, bool $newline = false, int $options = 0): void {}

    #[\Override]
    public function writeln(iterable|string $messages, int $options = 0): void {}
}
