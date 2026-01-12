<?php

declare(strict_types=1);

namespace IfCastle\DI;

abstract class InitializerAbstract implements InitializerInterface
{
    private bool $wasCalled = false;

    #[\Override]
    public function wasCalled(): bool
    {
        return $this->wasCalled;
    }

    #[\Override]
    public function executeInitializer(?ContainerInterface $container = null, array $resolvingKeys = []): mixed
    {
        $this->wasCalled = true;
        return $this->initialize($container, $resolvingKeys);
    }

    /**
     * @param array<class-string> $resolvingKeys list of classes that are currently being resolved
     */
    abstract protected function initialize(ContainerInterface $container, array $resolvingKeys = []): mixed;
}
