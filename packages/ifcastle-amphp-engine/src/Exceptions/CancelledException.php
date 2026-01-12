<?php

declare(strict_types=1);

namespace IfCastle\Amphp\Exceptions;

use IfCastle\Async\CancelledExceptionInterface;

class CancelledException extends \Amp\CancelledException implements CancelledExceptionInterface {}
