<?php

declare(strict_types=1);

namespace IfCastle\DI;

final class ConstructibleDependencyByReflection implements DependencyInterface, ConstructibleInterface
{
    /**
     * @var DescriptorInterface[]|null
     */
    private array|null $descriptors = null;

    public function __construct(
        private readonly string $className,
        private readonly bool $useConstructor = true,
        private readonly bool $resolveScalarAsConfig = true
    ) {}

    #[\Override]
    public function getDependencyName(): string
    {
        return $this->className;
    }

    #[\Override]
    public function getClassName(): string
    {
        return $this->className;
    }

    #[\Override]
    public function useConstructor(): bool
    {
        return $this->useConstructor;
    }

    #[\Override]
    public function getDependencyDescriptors(): array
    {
        if ($this->descriptors !== null) {
            return $this->descriptors;
        }

        $this->descriptors          = AttributesToDescriptors::readDescriptors($this->className, $this->resolveScalarAsConfig);

        return $this->descriptors;
    }
}
