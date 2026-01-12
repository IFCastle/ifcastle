<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader\Builder;

use IfCastle\DI\ConfigInterface;

final class BootloaderBuilderInMemory extends BootloaderBuilderAbstract
{
    /**
     * @param string[] $runtimeTags
     * @param array<class-string> $bootloaders
     * @param mixed[] $config
     */
    public function __construct(
        protected string $appDirectory,
        protected string $applicationType,
        protected array $runtimeTags,
        protected readonly array $bootloaders   = [],
        protected readonly array $config        = []
    ) {}

    #[\Override]
    protected function fetchBootloaders(): iterable
    {
        return $this->bootloaders;
    }

    #[\Override]
    protected function initConfigurator(): ConfigInterface
    {
        return new ConfigInMemory($this->config);
    }
}
