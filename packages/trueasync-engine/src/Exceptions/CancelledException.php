<?php

declare(strict_types=1);

namespace IfCastle\TrueAsync\Exceptions;

use Async\AsyncCancellation;
use IfCastle\Async\CancelledExceptionInterface;

/**
 * Cancellation sent by the engine; the reason given by the caller, if any, is the previous exception.
 */
final class CancelledException extends AsyncCancellation implements CancelledExceptionInterface {}
