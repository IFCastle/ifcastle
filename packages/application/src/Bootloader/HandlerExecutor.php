<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader;

use IfCastle\DesignPatterns\ExecutionPlan\HandlerExecutorInterface;
use IfCastle\DI\AutoResolverInterface;

final readonly class HandlerExecutor implements HandlerExecutorInterface
{
    /**
     * @var \WeakReference<BootloaderExecutorInterface>
     */
    private \WeakReference $bootloaderExecutor;

    /**
     * @var \WeakReference<BootloaderContextInterface>
     */
    private \WeakReference $bootloaderContext;

    public function __construct(BootloaderExecutorInterface $bootloaderExecutor, BootloaderContextInterface $bootloaderContext)
    {
        $this->bootloaderExecutor   = \WeakReference::create($bootloaderExecutor);
        $this->bootloaderContext    = \WeakReference::create($bootloaderContext);
    }


    #[\Override]
    public function executeHandler(mixed $handler, string $stage, mixed ...$parameters): mixed
    {
        $bootloaderContext          = $this->bootloaderContext->get();

        if ($bootloaderContext === null) {
            return null;
        }

        if ($handler instanceof BootloaderContextRequiredInterface) {
            $handler->setBootloaderContext($bootloaderContext);
        }

        if ($handler instanceof AutoResolverInterface) {
            $handler->resolveDependencies(
                $bootloaderContext->getSystemEnvironment()
                ?? throw new \Exception('System environment is required for AutoResolverInterface handler: ' . $handler::class)
            );
        }

        if ($handler instanceof BootloaderInterface) {
            $handler->buildBootloader($this->bootloaderExecutor->get());
        } elseif (\is_callable($handler)) {
            return $handler($stage, ...$parameters);
        }

        return null;
    }
}
