<?php

declare(strict_types=1);

namespace IfCastle\Application;

interface EngineInterface
{
    public function defineEngineRole(?EngineRolesEnum $engineRole = null): static;

    /**
     * Start of Engine.
     */
    public function start(): void;

    /**
     * Returns the Engine name that reflects the host.
     * For example, native, amp, swoole, roadrunner.
     */
    public function getEngineName(): string;

    /**
     * Returns the role of the current process.
     */
    public function getEngineRole(): EngineRolesEnum;

    /**
     * Returns TRUE if this is a server.
     */
    public function isServer(): bool;

    public function isProcess(): bool;

    public function isConsole(): bool;

    /**
     * Returns TRUE if this is a stateful server between requests.
     */
    public function isStateful(): bool;

    /**
     * Returns TRUE if the Engine supports asynchronous I/O and coroutines.
     */
    public function isAsynchronous(): bool;

    public function supportCoroutines(): bool;
}
