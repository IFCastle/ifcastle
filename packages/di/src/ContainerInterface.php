<?php

declare(strict_types=1);

namespace IfCastle\DI;

use IfCastle\DI\Exceptions\DependencyNotFound;

/**
 * Dependency container interface that behaves like a ServiceLocator.
 */
interface ContainerInterface
{
    /**
     * The method resolves a dependency.
     * If the dependency does not exist or an error occurs during its resolution,
     * the method throws an exception.
     *
     * If the dependency is undefined, the method also throws an exception `DependencyNotFound`.
     *
     * @template Class
     * @param class-string<Class>|string|DescriptorInterface $name
     * @param array<class-string>                            $resolvingKeys list of classes that are currently being resolved
     *
     * @return ($name is class-string ? Class : scalar|array<scalar>|null)
     * @throws DependencyNotFound
     */
    public function resolveDependency(
        string|DescriptorInterface  $name,
        ?DependencyInterface        $forDependency      = null,
        int                         $stackOffset        = 0,
        array                       $resolvingKeys      = [],
        bool                        $allowLazy          = true,
    ): mixed;

    /**
     * The method performs dependency lookup and resolution. If the dependency is not defined in the container,
     * the method will return NULL.
     * If the dependency resolution fails, the method will throw an exception.
     *
     * @template Class
     *
     * @param class-string<Class>|string|DescriptorInterface $name
     * @param array<class-string|string>                     $resolvingKeys list of classes that are currently being resolved
     *
     * @return ($name is class-string ? Class|null|\Throwable : scalar|array<scalar>|null|\Throwable)
     * @throws \Throwable
     */
    public function findDependency(
        string|DescriptorInterface      $name,
        ?DependencyInterface            $forDependency      = null,
        bool                            $returnThrowable    = false,
        array                           $resolvingKeys      = [],
        bool                            $allowLazy          = true,
    ): mixed;

    /**
     * The method returns the dependency if it is defined and has been resolved.
     * Otherwise, the method will return NULL.
     *
     * If the dependency resolution fails, the method will throw an exception.
     *
     * @template Class
     * @param class-string<Class>|string|DescriptorInterface    $name
     *
     * @return ($name is class-string ? Class|null : scalar|array<scalar>|null)
     * @throws \Throwable
     */
    public function getDependencyIfInitialized(string|DescriptorInterface $name): mixed;

    /**
     * @param class-string|string|DescriptorInterface $key
     */
    public function hasDependency(string|DescriptorInterface $key): bool;

    /**
     * The method will return the key by which the dependency can be found.
     * If the dependency does not exist, the method will return NULL.
     * This method is useful if the dependency is defined by multiple keys.
     * In that case, the method will return the original key.
     *
     * @param class-string|string|DescriptorInterface $key
     */
    public function findKey(string|DescriptorInterface $key): mixed;

    /**
     * The method will return the container label.
     *
     */
    public function getContainerLabel(): string;
}
