<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader\BootManager;

interface ComponentInterface
{
    public function getName(): string;

    public function defineDescription(string $description): static;

    public function getDescription(): string;

    public function isNew(): bool;

    public function isActivated(): bool;

    public function activate(): void;

    public function deactivate(): void;

    /**
     * @return array<string, scalar|null|mixed[]>
     */
    public function getGroups(): array;

    /**
     * @param string[]    $bootloaders
     * @param string[]    $applications
     * @param string[]    $runtimeTags
     * @param string[]    $excludeTags
     *
     * @return $this
     */
    public function add(
        array  $bootloaders,
        array  $applications    = [],
        array  $runtimeTags     = [],
        array  $excludeTags     = [],
        bool    $isActive       = true,
        ?string $group          = null
    ): static;

    public function deleteGroup(string $group): static;

    public function activateGroup(string $group): static;

    public function deactivateGroup(string $group): static;

    public function markAsSaved(): static;
}
