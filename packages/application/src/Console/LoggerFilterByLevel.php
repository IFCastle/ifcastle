<?php

declare(strict_types=1);

namespace IfCastle\Application\Console;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;

final readonly class LoggerFilterByLevel implements LoggerInterface
{
    use LoggerTrait;

    private int $logLevel;

    public function __construct(private LoggerInterface $logger, string $logLevel = LogLevel::WARNING)
    {
        $this->logLevel = self::levelToNumber($logLevel);
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        if (self::levelToNumber($level) >= $this->logLevel) {
            $this->logger->log($level, $message, $context);
        }
    }

    private static function levelToNumber(mixed $level): int
    {
        return match ($level) {
            LogLevel::DEBUG         => 1,
            LogLevel::INFO          => 2,
            LogLevel::NOTICE        => 3,
            LogLevel::WARNING       => 4,
            LogLevel::ERROR         => 5,
            LogLevel::CRITICAL      => 6,
            LogLevel::ALERT         => 7,
            LogLevel::EMERGENCY     => 8,
            default                 => 0,
        };
    }
}
