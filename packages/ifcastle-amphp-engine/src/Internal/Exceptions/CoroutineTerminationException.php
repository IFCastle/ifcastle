<?php

declare(strict_types=1);

namespace IfCastle\Amphp\Internal\Exceptions;

use IfCastle\Amphp\Internal\Coroutine;
use IfCastle\Exceptions\RuntimeException;

class CoroutineTerminationException extends RuntimeException
{
    protected string $template = 'Coroutine termination exception in {file}::{line}';

    protected array $tags = ['coroutine', 'amphp'];

    /**
     * @throws \ReflectionException
     */
    public function __construct(
        string          $message,
        Coroutine|null  $coroutine = null,
        ?\Throwable      $previous = null
    ) {
        $info                       = [
            'message'               => $message,
        ];

        if (null !== $coroutine?->getClosure()) {

            // get fine and line from the closure
            $reflection             = new \ReflectionFunction($coroutine->getClosure());

            if ($reflection->getFileName() !== false) {
                $info['file']       = $reflection->getFileName();
                $info['line']       = $reflection->getStartLine();
            }
        }

        parent::__construct($info,
            0, $previous);
    }
}
