<?php

declare(strict_types=1);

namespace IfCastle\TrueAsync;

use IfCastle\Async\CoroutineInterface;
use PHPUnit\Framework\Assert;
use Psr\Log\AbstractLogger;

use function Async\delay;

final class Support
{
    private const int WAIT_LIMIT_MS = 2000;

    /**
     * Fails the test instead of hanging when the coroutine does not finish in time.
     */
    public static function waitUntilFinished(CoroutineInterface $coroutine): void
    {
        for ($waited = 0; $waited < self::WAIT_LIMIT_MS; $waited++) {
            if ($coroutine->isFinished()) {
                return;
            }

            delay(1);
        }

        Assert::fail('Coroutine did not finish within ' . self::WAIT_LIMIT_MS . ' ms');
    }

    /**
     * @return AbstractLogger&object{messages: list<string>}
     */
    public static function collectingLogger(): AbstractLogger
    {
        return new class extends AbstractLogger {
            /** @var list<string> */
            public array $messages  = [];

            public function log($level, \Stringable|string $message, array $context = []): void
            {
                $this->messages[]   = $level . ': ' . $message;
            }
        };
    }
}
