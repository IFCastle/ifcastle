<?php

declare(strict_types=1);

namespace IfCastle\DI;

final class SelfReferenceInitializer implements InitializerInterface
{
    #[\Override]
    public function wasCalled(): bool
    {
        return false;
    }

    #[\Override]
    public function executeInitializer(?ContainerInterface $container = null, array $resolvingKeys = []): mixed
    {
        if ($container === null) {
            return null;
        }

        return \WeakReference::create($container);
    }
}
