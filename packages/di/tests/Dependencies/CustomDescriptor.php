<?php

declare(strict_types=1);

namespace IfCastle\DI\Dependencies;

use Attribute;
use IfCastle\DI\Dependency;
use IfCastle\DI\DescriptorInterface;
use IfCastle\DI\DescriptorProviderInterface;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class CustomDescriptor extends Dependency implements DescriptorProviderInterface
{
    #[\Override]
    public function getDescriptorProvider(): DescriptorProviderInterface|null
    {
        return $this;
    }

    #[\Override]
    public function provideDescriptor(
        DescriptorInterface $descriptor,
        \ReflectionClass $reflectionClass,
        \ReflectionParameter|\ReflectionProperty $reflectionTarget,
        object|string $object,
    ): DescriptorInterface {
        if ($descriptor !== $this) {
            throw new \TypeError('Descriptor is not an instance of ' . self::class);
        }

        $descriptor->key = 'customKey';

        // Return self descriptor.
        return $descriptor;
    }
}
