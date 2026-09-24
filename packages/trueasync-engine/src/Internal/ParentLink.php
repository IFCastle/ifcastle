<?php

declare(strict_types=1);

namespace IfCastle\TrueAsync\Internal;

use Async\Context;

/**
 * Link from a coroutine started by CoroutineScheduler::run() to the coroutine that started it.
 *
 * Stored in the child's own coroutine context under key(); TrueAsync does not inherit
 * coroutine contexts, so CoroutineContext walks these links to find inherited values.
 */
final readonly class ParentLink
{
    public function __construct(public Context $context, public int $coroutineId) {}

    /**
     * Context key under which the link is stored; an object, so it cannot collide with string keys.
     */
    public static function key(): object
    {
        static $key                 = new \stdClass();

        return $key;
    }
}
