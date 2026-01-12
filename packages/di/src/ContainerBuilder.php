<?php

declare(strict_types=1);

namespace IfCastle\DI;

class ContainerBuilder implements BuilderInterface
{
    /**
     * @var array<string, DependencyInterface|InitializerInterface|object|\Throwable|\WeakReference|scalar|null>
     */
    protected array $bindings       = [];

    /**
     * @param bool $useDeferredReflection   Specifies to use reflection for analyzing dependencies only at the moment the dependency is used.
     * @param bool $resolveScalarAsConfig   Specifies to resolve scalar values as configuration dependency-values.
     */
    public function __construct(
        protected bool $useDeferredReflection = false,
        protected bool $resolveScalarAsConfig = true
    ) {}

    #[\Override]
    public function isBound(string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (\array_key_exists($key, $this->bindings)) {
                return true;
            }
        }

        return false;
    }

    #[\Override]
    public function bind(
        array|string                             $interface,
        DependencyInterface|InitializerInterface $dependency,
        bool                                     $isThrow = true,
        bool                                     $redefine = false
    ): static {
        $keys                       = \is_array($interface) ? $interface : [$interface];
        $firstKey                   = \array_shift($keys);

        if (false === $redefine && \array_key_exists($firstKey, $this->bindings)) {
            if ($isThrow) {
                throw new \InvalidArgumentException("Interface '$firstKey' already bound to '" . $this->getKeyAsString($firstKey) . "'");
            }

            return $this;

        }

        if ($redefine && \array_key_exists($firstKey, $this->bindings) && $this->bindings[$firstKey] instanceof AliasInitializer) {
            $alias                  = $this->bindings[$firstKey]->alias;

            if ($alias !== $firstKey) {
                $keys[]             = $firstKey;
                $firstKey           = $alias;
            }
        }

        $this->bindings[$firstKey]  = $dependency;

        foreach ($keys as $key) {

            if (false === $redefine && \array_key_exists($key, $this->bindings)) {
                if ($isThrow) {
                    throw new \InvalidArgumentException("Interface '$key' already bound to '" . $this->getKeyAsString($key) . "'");
                }

                continue;

            }

            $this->bindings[$key]    = new AliasInitializer($firstKey);
        }

        return $this;
    }

    #[\Override]
    public function bindConstructible(array|string $interface, string $class, bool $isThrow = true, bool $redefine = false): static
    {
        // special case: AutoResolverInterface
        if (\is_subclass_of($class, AutoResolverInterface::class)) {
            throw new \InvalidArgumentException('AutoResolverInterface cannot be used as constructible dependency.'
                                                . ' Please, use bindInjectable instead.');
        }

        return $this->bind(
            $interface,
            $this->useDeferredReflection ? new ConstructibleDependencyByReflection($class, true, $this->resolveScalarAsConfig) :
            new ConstructibleDependency($class, true, AttributesToDescriptors::readDescriptors($class, $this->resolveScalarAsConfig)),
            $isThrow,
            $redefine
        );
    }

    #[\Override]
    public function bindInjectable(array|string $interface, string $class, bool $isThrow = true, bool $redefine = false): static
    {
        return $this->bind(
            $interface,
            $this->useDeferredReflection ? new ConstructibleDependencyByReflection($class, false, $this->resolveScalarAsConfig) :
            new ConstructibleDependency($class, false, AttributesToDescriptors::readDescriptors($class, $this->resolveScalarAsConfig)),
            $isThrow,
            $redefine
        );
    }

    #[\Override]
    public function bindObject(array|string $interface, object $object, bool $isThrow = true, bool $redefine = false): static
    {
        if ($object instanceof InitializerInterface || $object instanceof DependencyInterface) {
            throw new \InvalidArgumentException('Object cannot be used as dependency or initializer');
        }

        foreach (\is_array($interface) ? $interface : [$interface] as $key) {

            if (false === $redefine && \array_key_exists($key, $this->bindings)) {
                if ($isThrow) {
                    throw new \InvalidArgumentException(
                        "Interface '$key' already bound to '" . $this->getKeyAsString($key) . "'"
                    );
                }

                continue;

            }

            $this->bindings[$key]    = $object;
        }

        return $this;
    }

    #[\Override]
    public function bindSelfReference(
        array|string|null $interface     = null,
        bool         $isThrow       = true,
        bool         $redefine      = false
    ): static {
        if (null === $interface) {
            $interface               = ContainerInterface::class;
        }

        return $this->bind(
            $interface,
            new SelfReferenceInitializer(),
            $isThrow,
            $redefine
        );
    }

    #[\Override]
    public function bindInitializer(
        array|string $interface,
        callable     $initializer,
        bool         $isThrow       = true,
        bool         $redefine      = false
    ): static {
        return $this->bind(
            $interface,
            new Initializer($initializer),
            $isThrow,
            $redefine
        );
    }

    #[\Override]
    public function get(string $key): mixed
    {
        return $this->bindings[$key] ?? null;
    }

    #[\Override]
    public function set(string $key, mixed $value): static
    {
        if (\array_key_exists($key, $this->bindings)) {
            throw new \InvalidArgumentException("Key '$key' already defined");
        }

        $this->bindings[$key]       = $value;

        return $this;
    }

    #[\Override]
    public function buildContainer(
        ResolverInterface  $resolver,
        ?ContainerInterface $parentContainer = null,
        bool               $isWeakParent = false
    ): ContainerInterface {
        $bindings                   = $this->bindings;
        $this->bindings             = [];

        return new Container($resolver, $bindings, $parentContainer, $isWeakParent);
    }

    #[\Override]
    public function getKeyAsString(string $key): string
    {
        if (false === \array_key_exists($key, $this->bindings)) {
            return 'undefined';
        }

        $value                      = $this->bindings[$key];

        if ($value instanceof AliasInitializer) {

            if (\array_key_exists($value->alias, $this->bindings)) {
                return 'alias: ' . $value->alias . ' -> ' . $this->getKeyAsString($value->alias);
            }

            return 'alias: ' . $value->alias . ' -> undefined';
        }

        if ($value instanceof ConstructibleInterface) {
            return 'dependency: ' . $value->getClassName();
        } elseif (\is_object($value)) {
            return 'object: ' . $value::class;
        }

        return 'type: ' . \get_debug_type($value);

    }
}
