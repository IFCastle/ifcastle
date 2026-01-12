<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader\BootManager;

interface BootManagerInterface
{
    public function createComponent(string $componentName): ComponentInterface;

    public function getComponent(string $componentName): ComponentInterface;

    public function addComponent(ComponentInterface $component): void;

    public function updateComponent(ComponentInterface $component): void;

    public function removeComponent(string $componentName): void;
}
