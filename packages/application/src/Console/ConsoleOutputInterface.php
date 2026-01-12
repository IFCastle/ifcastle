<?php

declare(strict_types=1);

namespace IfCastle\Application\Console;

/**
 * Interface for classes that can write to the console output.
 * Compatibility with Symfony\Component\Console\Output\OutputInterface.
 */
interface ConsoleOutputInterface
{
    public const int VERBOSITY_QUIET        = 16;

    public const int VERBOSITY_NORMAL       = 32;

    public const int VERBOSITY_VERBOSE      = 64;

    public const int VERBOSITY_VERY_VERBOSE = 128;

    public const int VERBOSITY_DEBUG        = 256;

    public const int OUTPUT_NORMAL = 1;

    public const int OUTPUT_RAW   = 2;

    public const int OUTPUT_PLAIN = 4;

    /**
     * Writes a message to the output.
     *
     * @param string|iterable<string> $messages The message as an iterable of strings or a single string
     * @param bool            $newline  Whether to add a newline
     * @param int             $options  A bitmask of options (one of the OUTPUT or VERBOSITY constants),
     *                                  0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */
    public function write(string|iterable $messages, bool $newline = false, int $options = 0): void;

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|iterable<string> $messages The message as an iterable of strings or a single string
     * @param int $options A bitmask of options (one of the OUTPUT or VERBOSITY constants),
     *                     0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */
    public function writeln(string|iterable $messages, int $options = 0): void;
}
