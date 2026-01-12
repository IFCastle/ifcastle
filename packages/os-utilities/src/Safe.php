<?php

declare(strict_types=1);

namespace IfCastle\OsUtilities;

final class Safe
{
    /**
     * @throws \ErrorException
     */
    public static function execute(callable $callback, ?callable $throwConstructor = null): mixed
    {
        $error                      = null;

        \set_error_handler(
            function (int $severity, string $message, string $file, int $line) use (&$error, $throwConstructor) {

                if ($throwConstructor !== null) {
                    $error          = \call_user_func($throwConstructor, $message, $severity);
                } else {
                    $error          = new \ErrorException($message, 0, $severity, $file, $line);
                }
            }
        );

        try {
            $result                 = \call_user_func($callback);
        } finally {
            \restore_error_handler();
        }

        if ($error !== null) {
            throw $error;
        }

        return $result;
    }
}
