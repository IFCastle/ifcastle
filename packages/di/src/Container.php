<?php

declare(strict_types=1);

namespace IfCastle\DI;

use IfCastle\DI\Exceptions\DependencyNotFound;

class Container implements NestedContainerInterface, DisposableInterface
{
    /** @var \WeakReference<ContainerInterface>|ContainerInterface|null */
    private \WeakReference|ContainerInterface|null $parentContainer = null;

    public function __construct(
        protected ResolverInterface $resolver,
        /** @var array<class-string|string, DependencyInterface|InitializerInterface|object|\Throwable|\WeakReference|scalar|null> */
        protected array $container  = [],
        ?ContainerInterface $parentContainer = null,
        bool $isWeakParent          = false
    ) {
        if (null !== $parentContainer) {
            $this->parentContainer  = $isWeakParent ? \WeakReference::create($parentContainer) : $parentContainer;
        }

        // add self-reference to container
        if (\array_key_exists(ContainerInterface::class, $this->container)) {
            $this->container[ContainerInterface::class] = \WeakReference::create($this);
        }
    }

    #[\Override]
    public function getParentContainer(): ContainerInterface|null
    {
        if ($this->parentContainer instanceof \WeakReference) {
            return $this->parentContainer->get();
        }

        return $this->parentContainer;
    }

    #[\Override]
    public function resolveDependency(
        string|DescriptorInterface  $name,
        ?DependencyInterface        $forDependency      = null,
        int                         $stackOffset        = 0,
        array                       $resolvingKeys      = [],
        bool                        $allowLazy          = true,
    ): mixed {
        $dependency                 = $this->findDependency($name, $forDependency, true, $resolvingKeys, $allowLazy);

        if (null === $dependency) {

            if ($name instanceof DescriptorInterface && false === $name->isRequired()) {
                return null;
            }

            throw new DependencyNotFound($name, $this, $forDependency, $stackOffset + 3);
        }

        if ($dependency instanceof \Throwable) {
            throw $dependency;
        }

        return $dependency;
    }

    /**
     * @throws \Throwable
     */
    #[\Override]
    public function findDependency(
        string|DescriptorInterface  $name,
        ?DependencyInterface        $forDependency      = null,
        bool                        $returnThrowable    = false,
        array                       $resolvingKeys      = [],
        bool                        $allowLazy          = true,
    ): mixed {
        $key                        = $this->findKey($name);

        if (null === $key) {
            return $this->getParentContainer()?->findDependency($name, $forDependency, $returnThrowable, $resolvingKeys, $allowLazy);
        }

        $dependency                 = $this->container[$key];

        if ($dependency instanceof \Throwable) {
            return $returnThrowable ? $dependency : throw $dependency;
        }

        if ($dependency instanceof InitializerInterface) {

            try {
                $this->container[$key] = $dependency->executeInitializer($this, $resolvingKeys);

                if ($this->container[$key] instanceof \WeakReference) {
                    return $this->container[$key]->get();
                }

                return $this->container[$key];
            } catch (\Throwable $exception) {
                $this->container[$key] = $exception;
                return $returnThrowable ? $exception : throw $exception;
            }
        }

        if ($dependency instanceof \WeakReference) {
            return $dependency->get();
        }

        if (false === $dependency instanceof DependencyInterface) {
            return $dependency;
        }

        if ($this->resolver->canResolveDependency($dependency, $this)) {

            try {
                $this->container[$key] = $this->resolver->resolveDependency($dependency, $this, $name, $key, $resolvingKeys, $allowLazy);

                if ($this->container[$key] instanceof \WeakReference) {
                    return $this->container[$key]->get();
                }

                return $this->container[$key];
            } catch (\Throwable $exception) {
                $this->container[$key] = $exception;
                return $returnThrowable ? $exception : throw $exception;
            }
        }

        return null;
    }

    /**
     * @throws \Throwable
     */
    #[\Override]
    public function getDependencyIfInitialized(string|DescriptorInterface $name): mixed
    {
        $key                        = $this->findKey($name);

        if (null === $key) {
            return $this->getParentContainer()?->getDependencyIfInitialized($name);
        }

        $dependency                 = $this->container[$key];

        if ($dependency instanceof \Throwable) {
            throw $dependency;
        }

        if ($dependency instanceof InitializerInterface || $dependency instanceof DependencyInterface) {
            return null;
        }

        return $dependency;
    }

    #[\Override]
    public function hasDependency(string|DescriptorInterface $key): bool
    {
        if ($this->findKey($key) !== null) {
            return true;
        }

        return $this->getParentContainer()?->hasDependency($key) ?? false;
    }

    #[\Override]
    public function findKey(DescriptorInterface|string $key): string|null
    {
        if (\is_string($key) && \array_key_exists($key, $this->container)) {
            return $key;
        }

        if (\is_string($key)) {
            return null;
        }

        $type                   = $key->getDependencyType();

        foreach (\array_merge([$key->getDependencyKey()], \is_array($type) ? $type : [$type]) as $key) {
            if ($key !== null && \array_key_exists($key, $this->container)) {
                return $key;
            }
        }

        return null;
    }

    #[\Override]
    public function getContainerLabel(): string
    {
        return 'container';
    }

    #[\Override]
    public function dispose(): void
    {
        $container                  = $this->container;
        $this->container            = [];

        $errors                     = [];

        foreach ($container as $key => $dependency) {
            if ($dependency instanceof DisposableInterface) {
                try {
                    $dependency->dispose();
                } catch (\Throwable $exception) {
                    $errors[$key]   = $exception;
                }
            }
        }

        if (\count($errors) === 1) {
            throw \array_pop($errors);
        }

        if (\count($errors) > 1) {
            throw new \Error('Multiple errors occurred during disposal');
        }
    }

    protected function redefineParentContainer(?ContainerInterface $parentContainer = null, bool $isWeakParent = false): void
    {
        if ($parentContainer === null) {
            $this->parentContainer  = null;
            return;
        }

        $this->parentContainer  = $isWeakParent ? \WeakReference::create($parentContainer) : $parentContainer;
    }
}
