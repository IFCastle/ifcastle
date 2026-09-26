<?php

declare(strict_types=1);

namespace IfCastle\Async;

interface CoroutineContextInterface
{
    public function isCoroutine(): bool;

    public function getCoroutineId(): string|int;

    public function getCoroutineParentId(): string|int;

    public function has(string $key): bool;

    public function get(string $key): mixed;

    public function getLocal(string $key): mixed;

    public function hasLocal(string $key): bool;

    public function set(string $key, mixed $value): static;

    /**
     * A value shared by every coroutine that serves the current request, children included, and
     * gone with the request. Outside a request, or on an engine that has no request scope, it is
     * the same as get().
     */
    public function getForRequest(string $key): mixed;

    /**
     * Stores a value for every coroutine that serves the current request; see getForRequest().
     * Outside a request, or on an engine that has no request scope, it is the same as set().
     *
     * @return $this
     */
    public function setForRequest(string $key, mixed $value): static;

    /**
     * Call the callback when the coroutine is destroyed.
     *
     *
     * @return $this
     */
    public function defer(callable $callback): static;
}
