<?php

declare(strict_types=1);

namespace IfCastle\Application\RequestEnvironment;

use IfCastle\DesignPatterns\ExecutionPlan\ExecutionPlanInterface;

interface RequestPlanInterface extends ExecutionPlanInterface
{
    public const string RAW_BUILD           = '-b';

    public const string BUILD               = 'b';

    public const string BEFORE_DISPATCH     = '-d';

    public const string DISPATCH            = 'd';

    public const string BEFORE_EXECUTE      = '-e';

    public const string EXECUTE             = 'e';

    public const string RESPONSE            = 'r';

    public const string AFTER_RESPONSE      = '+r';

    public const string FINALLY             = 'f';

    public function addRawBuildHandler(callable $handler): static;

    public function addBuildHandler(callable $handler): static;

    public function addBeforeDispatchHandler(callable $handler): static;

    public function addDispatchHandler(callable $handler): static;

    public function addBeforeHandleHandler(callable $handler): static;

    public function addExecuteHandler(callable $handler): static;

    public function addResponseHandler(callable $handler): static;

    public function addAfterResponseHandler(callable $handler): static;

    public function addFinallyHandler(callable $handler): static;
}
