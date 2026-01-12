<?php

declare(strict_types=1);

namespace IfCastle\Application\Console;

use Psr\Log\LoggerInterface;

interface ConsoleLoggerInterface extends LoggerInterface
{
    public const string PID         = 'workerPid';

    public const string WORKER      = 'worker';

    public const string STATUS      = 'appStatus';

    public const string IS_FAILURE  = 'appStatusIsFailure';

    public const string NO_TIMESTAMP = 'outNoTimestamp';

    public const string IN_FRAME    = 'outInFrame';

    public const string VERSION     = 'appVersion';
}
