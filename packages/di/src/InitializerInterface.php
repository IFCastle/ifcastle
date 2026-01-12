<?php

declare(strict_types=1);

namespace IfCastle\DI;

/**
 * ## Container for InitializerInterface.
 *
 * ### Usage
 * For lazy object initialization
 *
 * Used in conjunction with the Environment class.
 *
 * ## Algorithm
 *
 * 1. An environment variable is defined as a class `InitializerInterface` instance
 * 2. Someone is trying to access a variable
 * 3. The initialization method `executeInitializer` is called
 */
interface InitializerInterface
{
    /**
     * ## Check if the initializer has been called.
     */
    public function wasCalled(): bool;

    /**
     * ## Execute the initializer.
     *
     * @param array<class-string> $resolvingKeys list of classes that are currently being resolved
     */
    public function executeInitializer(?ContainerInterface $container = null, array $resolvingKeys = []): mixed;
}
