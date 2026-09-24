<?php

declare(strict_types=1);

namespace IfCastle\TrueAsync\Internal;

use Psr\Log\LoggerInterface;

use function Async\current_coroutine;

/**
 * Keeps an error inside the coroutine that raised it.
 *
 * TrueAsync hands an error escaping a coroutine to its Scope, which cancels every coroutine in it
 * and ends the process when no handler exists. Engine code runs callbacks through call() instead,
 * so a failing callback costs one log entry.
 */
final readonly class ErrorReporter
{
    /**
     * @param LoggerInterface|null $logger Without a logger, or when the logger itself fails, errors go to error_log().
     */
    public function __construct(private ?LoggerInterface $logger = null) {}

    /**
     * Calls the callback and reports what it throws. Cancellation of the current coroutine is
     * rethrown, so cancel() still ends it; any other cancellation, such as a fired token, is reported.
     */
    public function call(callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $throwable) {
            if ($throwable instanceof \Cancellation && current_coroutine()->isCancellationRequested()) {
                throw $throwable;
            }

            $this->report($throwable);
        }
    }

    public function report(\Throwable $throwable): void
    {
        if ($this->logger === null) {
            \error_log((string) $throwable);
            return;
        }

        try {
            $this->logger->error($throwable->getMessage(), ['exception' => $throwable]);
        } catch (\Throwable $loggerError) {
            \error_log((string) $throwable);
            \error_log((string) $loggerError);
        }
    }
}
