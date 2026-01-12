<?php

declare(strict_types=1);

namespace IfCastle\Application\Console;

/**
 * A lightweight version of console output that works without Symfony components.
 *
 */
final readonly class ConsoleOutput implements ConsoleOutputInterface
{
    public function __construct(private int $verbosity = self::VERBOSITY_NORMAL) {}


    #[\Override]
    public function write(iterable|string $messages, bool $newline = false, int $options = 0): void
    {
        if (\is_string($messages)) {
            $messages = [$messages];
        }

        $types                      = self::OUTPUT_NORMAL | self::OUTPUT_RAW | self::OUTPUT_PLAIN;
        $type                       = $types & $options ?: self::OUTPUT_NORMAL;

        $verbosities                = self::VERBOSITY_QUIET | self::VERBOSITY_NORMAL | self::VERBOSITY_VERBOSE
                                      | self::VERBOSITY_VERY_VERBOSE | self::VERBOSITY_DEBUG;
        $verbosity                  = $verbosities & $options ?: self::VERBOSITY_NORMAL;

        if ($verbosity < $this->verbosity) {
            return;
        }

        foreach ($messages as $message) {
            switch ($type) {
                case self::OUTPUT_NORMAL:
                    $message = $this->format($message);
                    break;
                case self::OUTPUT_RAW:
                    break;
                case self::OUTPUT_PLAIN:
                    $message = \strip_tags($this->format($message));
                    break;
            }

            $this->doWrite($message, $newline);
        }
    }

    #[\Override]
    public function writeln(iterable|string $messages, int $options = 0): void
    {
        $this->write($messages, true, $options);
    }

    protected function format(string $message): string
    {
        return \strip_tags($message);
    }

    protected function doWrite(string $message, bool $newline): void
    {
        if ($message === '' && !$newline) {
            return;
        }

        echo $message . ($newline ? PHP_EOL : '');
    }
}
