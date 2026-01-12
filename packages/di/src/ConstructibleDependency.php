<?php

declare(strict_types=1);

namespace IfCastle\DI;

final readonly class ConstructibleDependency implements DependencyInterface, ConstructibleInterface
{
    /**
     * ConstructibleDependency constructor.
     *
     * @param DescriptorInterface[] $descriptors
     */
    public function __construct(
        private string $className,
        private bool   $useConstructor = true,
        private array  $descriptors = []
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
        return $this->descriptors;
    }
}
