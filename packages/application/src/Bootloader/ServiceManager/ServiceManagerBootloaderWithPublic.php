<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader\ServiceManager;

use IfCastle\Application\Bootloader\BootloaderContextInterface;
use IfCastle\Application\Bootloader\BootloaderContextRequiredInterface;
use IfCastle\Application\Environment\PublicEnvironmentInterface;
use IfCastle\Application\Environment\SystemEnvironmentInterface;
use IfCastle\DI\AutoResolverInterface;
use IfCastle\DI\ContainerInterface;
use IfCastle\DI\DisposableInterface;
use IfCastle\DI\ResolverInterface;
use IfCastle\ServiceManager\DescriptorRepository;
use IfCastle\ServiceManager\DescriptorRepositoryInterface;
use IfCastle\ServiceManager\ExecutorInterface;
use IfCastle\ServiceManager\RepositoryStorages\RepositoryReaderByTagsBridge;
use IfCastle\ServiceManager\RepositoryStorages\RepositoryReaderInterface;
use IfCastle\ServiceManager\RepositoryStorages\ServiceCollectionInterface;
use IfCastle\ServiceManager\ServiceDescriptorBuilderInterface;
use IfCastle\ServiceManager\ServiceLocator;
use IfCastle\ServiceManager\ServiceLocatorInterface;

final class ServiceManagerBootloaderWithPublic implements AutoResolverInterface, BootloaderContextRequiredInterface, DisposableInterface
{
    protected SystemEnvironmentInterface|null $systemEnvironment = null;

    protected BootloaderContextInterface|null $bootloaderContext = null;

    #[\Override]
    public function setBootloaderContext(BootloaderContextInterface $bootloaderContext): void
    {
        $this->bootloaderContext    = $bootloaderContext;
    }

    #[\Override]
    public function resolveDependencies(ContainerInterface $container): void
    {
        if ($container instanceof SystemEnvironmentInterface) {
            $this->systemEnvironment = $container;
        }
    }

    #[\Override]
    public function dispose(): void
    {
        $this->systemEnvironment    = null;
    }

    public function __invoke(): void
    {
        $sysEnv                     = $this->systemEnvironment ?? $this->bootloaderContext?->getSystemEnvironment();

        if ($sysEnv === null) {
            throw new \Exception('System environment is required for ServiceManagerBootloader');
        }

        $publicEnvironment          = $sysEnv->resolveDependency(PublicEnvironmentInterface::class);
        $serviceCollection          = $sysEnv->resolveDependency(ServiceCollectionInterface::class);

        $publicReader               = new RepositoryReaderByTagsBridge($serviceCollection, $this->defineRuntimeTags());
        $internalReader             = new RepositoryReaderByTagsBridge($serviceCollection, []);

        $sysEnv->set(RepositoryReaderInterface::class, $internalReader);
        $publicEnvironment->set(RepositoryReaderInterface::class, $publicReader);

        if (false === $this->systemEnvironment->hasDependency(DescriptorRepositoryInterface::class)) {

            $descriptorRepository = new DescriptorRepository(
                $internalReader,
                $sysEnv->resolveDependency(ResolverInterface::class),
                $sysEnv->resolveDependency(ServiceDescriptorBuilderInterface::class)
            );

            $this->systemEnvironment->set(DescriptorRepositoryInterface::class, $descriptorRepository);
        }

        if (false === $publicEnvironment->hasDependency(DescriptorRepositoryInterface::class)) {
            $publicEnvironment->set(
                DescriptorRepositoryInterface::class,
                new DescriptorRepository(
                    $publicReader,
                    $publicEnvironment->resolveDependency(ResolverInterface::class),
                    $publicEnvironment->resolveDependency(ServiceDescriptorBuilderInterface::class)
                )
            );
        }

        if (false === $sysEnv->hasDependency(ServiceLocatorInterface::class)) {
            $sysEnv->set(
                ServiceLocatorInterface::class,
                new ServiceLocator($sysEnv->resolveDependency(DescriptorRepositoryInterface::class))
            );
        }

        if (false === $publicEnvironment->hasDependency(ServiceLocatorInterface::class)) {
            $publicEnvironment->set(
                ServiceLocatorInterface::class,
                new ServiceLocator($publicEnvironment->resolveDependency(DescriptorRepositoryInterface::class))
            );
        }

        if (false === $sysEnv->hasDependency(ExecutorInterface::class)) {
            $sysEnv->set(ExecutorInterface::class, new InternalExecutorInitializer());
        }

        if (false === $publicEnvironment->hasDependency(ExecutorInterface::class)) {
            $publicEnvironment->set(ExecutorInterface::class, new PublicExecutorInitializer());
        }

        $this->dispose();
    }

    /**
     * Define the runtime tags for the Service Manager.
     * @return array<string>
     */
    protected function defineRuntimeTags(): array
    {
        return $this->systemEnvironment->getRuntimeTags();
    }
}
