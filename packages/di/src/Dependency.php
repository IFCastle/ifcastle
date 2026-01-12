<?php

declare(strict_types=1);

namespace IfCastle\DI;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Dependency implements DescriptorInterface
{
    public ProviderInterface|null $provider = null;

    public DescriptorProviderInterface|null $descriptorProvider = null;

    public function __construct(
        public string $key               = '',
        /** @var string|string[]|null */
        public string|array|null $type   = null,
        public bool $isRequired          = true,
        public bool $isLazy              = false,
        public string $property          = '',
        public bool $hasDefaultValue     = false,
        public mixed $defaultValue       = null
    ) {}

    #[\Override]
    public function getDependencyKey(): string
    {
        return $this->key;
    }

    #[\Override]
    public function getDependencyProperty(): string
    {
        return $this->property;
    }

    #[\Override]
    public function getDependencyType(): string|array|null
    {
        return $this->type;
    }

    #[\Override]
    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    #[\Override]
    public function isLazy(): bool
    {
        return $this->isLazy;
    }

    #[\Override]
    public function getProvider(): ProviderInterface|null
    {
        return $this->provider;
    }

    #[\Override]
    public function getDescriptorProvider(): DescriptorProviderInterface|null
    {
        return $this->descriptorProvider;
    }

    #[\Override]
    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    #[\Override]
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }
}
