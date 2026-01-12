<?php

declare(strict_types=1);

namespace IfCastle\DI;

use IfCastle\DI\Exceptions\CircularDependencyException;
use IfCastle\DI\Exceptions\DependencyNotFound;
use IfCastle\DI\Exceptions\MaxResolutionDepthException;

class Resolver implements ResolverInterface
{
    /**
     * @param DescriptorInterface[] $dependencies
     * @param array<class-string|string> $resolvingKeys list of classes that are currently being resolved
     *
     * @return mixed[]
     * @throws DependencyNotFound
     * @throws \Throwable
     */
    public static function resolveDependencies(
        ContainerInterface $container,
        array $dependencies,
        DependencyInterface $forDependency,
        array $resolvingKeys        = [],
        bool  $allowLazy            = true,
    ): array {
        $resolvedDependencies       = [];

        foreach ($dependencies as $descriptor) {

            // special case: if the dependency is already initialized for LazyLoad, we can skip the resolution
            if ($descriptor->isLazy()
               && $descriptor->getProvider() === null
               && null !== ($object = $container->getDependencyIfInitialized($descriptor))) {
                $resolvedDependencies[] = $object;
            } else {
                $resolvedDependencies[] = static::resolve($container, $descriptor, $forDependency, 0, $resolvingKeys, $allowLazy);
            }
        }

        return $resolvedDependencies;
    }

    /**
     * @param array<class-string|string> $resolvingKeys list of classes that are currently being resolved
     *
     * @throws DependencyNotFound
     */
    public static function resolve(
        ContainerInterface  $container,
        DescriptorInterface $descriptor,
        DependencyInterface $forDependency,
        int                 $stackOffset    = 0,
        array               $resolvingKeys  = [],
        bool                $allowLazy      = true,
    ): mixed {
        $object                 = $descriptor->getProvider()?->provide($container, $descriptor, $forDependency, $resolvingKeys);

        if ($object !== null) {
            return $object;
        }

        if ($descriptor->getProvider() !== null) {

            if ($descriptor->hasDefaultValue()) {
                return $descriptor->getDefaultValue();
            }

            if ($descriptor->isRequired()) {
                throw new DependencyNotFound($descriptor, $container, $forDependency, $stackOffset + 5);
            }

            return null;
        }

        return $container->resolveDependency($descriptor, $forDependency, $stackOffset + 6, $resolvingKeys, $allowLazy);
    }

    #[\Override]
    public function canResolveDependency(DependencyInterface $dependency, ContainerInterface $container): bool
    {
        return $dependency instanceof ConstructibleInterface;
    }

    /**
     * @throws \ReflectionException
     * @throws DependencyNotFound
     * @throws MaxResolutionDepthException
     */
    #[\Override]
    public function resolveDependency(
        DependencyInterface $dependency,
        ContainerInterface $container,
        string|DescriptorInterface  $name,
        string $key,
        array $resolvingKeys        = [],
        bool $allowLazy             = false,
    ): mixed {

        if (false === $dependency instanceof ConstructibleInterface) {
            throw new \Error('descriptor must implement ConstructibleInterface');
        }

        //
        // Use Proxy to avoid circular dependencies
        // or if the dependency is lazy.
        //
        if (\in_array($key, $resolvingKeys, true) || ($name instanceof DescriptorInterface && $name->isLazy())) {

            if (false === $allowLazy) {
                throw new CircularDependencyException($name, $container, $resolvingKeys);
            }

            $containerRef           = \WeakReference::create($container);
            $resolverRef            = \WeakReference::create($this);

            //
            // Use Proxy to avoid circular dependencies.
            // If a circular dependency resolution is detected, we stop the resolution process
            // and return a Proxy object that will later point to the actual dependency.
            //
            return new \ReflectionClass($dependency->getClassName())->newLazyProxy(
                static function () use ($containerRef, $resolverRef, $dependency, $resolvingKeys, $key) {

                    $container      = $containerRef->get();
                    $resolver       = $resolverRef->get();

                    if ($container === null || $resolver === null) {
                        return null;
                    }

                    if (false === \in_array($key, $resolvingKeys, true)) {
                        $resolvingKeys[] = $key;
                    }

                    return $resolver->instanciateDependency($dependency, $container, $resolvingKeys, allowLazy: false);
                });
        }

        $resolvingKeys[]            = $key;

        if (\count($resolvingKeys) > 32) {
            throw new MaxResolutionDepthException(32, $resolvingKeys);
        }

        return $this->instanciateDependency($dependency, $container, $resolvingKeys, $allowLazy);
    }

    /**
     * @param array<class-string> $resolvingKeys list of classes that are currently being resolved
     *
     * @throws \ReflectionException
     * @throws DependencyNotFound
     */
    protected function instanciateDependency(
        DependencyInterface & ConstructibleInterface $dependency,
        ContainerInterface $container,
        array $resolvingKeys        = [],
        bool  $allowLazy            = false,
    ): mixed {
        $dependencies               = static::resolveDependencies(
            $container, $dependency->getDependencyDescriptors(), $dependency, $resolvingKeys, $allowLazy
        );

        if ($dependency->useConstructor()) {
            $className              = $dependency->getClassName();
            return new $className(...$dependencies);
        }

        $className                  = $dependency->getClassName();
        $object                     = new $className();

        if ($object instanceof InjectableInterface) {
            return $object->injectDependencies($dependencies, $dependency)->initializeAfterInject();
        } elseif ($object instanceof AutoResolverInterface) {
            $object->resolveDependencies($container);
        }

        return $object;
    }
}
