<?php

declare(strict_types=1);

namespace IfCastle\Amphp\Internal\Exceptions;

use IfCastle\Amphp\Internal\Coroutine;

class CoroutineNotStarted extends CoroutineTerminationException
{
    public function __construct(Coroutine $coroutine)
    {
        parent::__construct('Coroutine not started', $coroutine);
    }
}
