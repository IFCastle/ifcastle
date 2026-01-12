<?php

declare(strict_types=1);

namespace IfCastle\Amphp\Internal\Exceptions;

class CoroutineTimeLimit extends CoroutineTerminationException
{
    public function __construct(int $timeLimit)
    {
        parent::__construct("Coroutine time limit of $timeLimit seconds exceeded.");
    }
}
