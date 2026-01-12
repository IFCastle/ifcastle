<?php

declare(strict_types=1);

namespace IfCastle\DI;

final class Initializer implements InitializerInterface, DisposableInterface
{
    private mixed $handler;

    public function __construct(callable $handler)
    {
        $this->handler              = $handler;
    }

    #[\Override]
    public function wasCalled(): bool
    {
        return $this->handler === null;
    }

    #[\Override]
    public function executeInitializer(?ContainerInterface $container = null, array $resolvingKeys = []): mixed
    {
        $handler                    = $this->handler;

        $this->handler              = null;

        return $handler($container);
    }

    #[\Override]
    public function dispose(): void
    {
        if ($this->handler instanceof DisposableInterface) {
            $this->handler->dispose();
        }

        $this->handler              = null;
    }
}
