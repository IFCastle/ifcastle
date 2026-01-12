<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

interface ServiceManagerInterface
{
    public function installService(ServiceDescriptorInterface $serviceDescriptor): void;

    public function uninstallService(string $serviceName, string $packageName): void;

    public function activateService(string $packageName, string $serviceName, string $suffix): void;

    public function deactivateService(string $packageName, string $serviceName, string $suffix): void;

    public function updateServiceConfig(ServiceDescriptorInterface $serviceDescriptor): void;
}
