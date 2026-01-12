<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader\BootManager;

final class Component implements ComponentInterface
{
    /**
     * @var array<string, scalar|null|mixed[]> $groups
     */
    private array $groups       = [];

    private string $description  = '';

    private bool $isActivated    = true;

    private bool $isNew;

    /**
     * Component constructor.
     *
     * @param array<string, scalar|null|mixed[]>|null $groups
     */
    public function __construct(public string $name, array|null $groups = null)
    {
        if ($groups !== null) {

            if (\array_key_exists('description', $groups)) {
                $this->description   = $groups['description'];
                unset($groups['description']);
            }

            if (\array_key_exists('isActive', $groups)) {
                $this->isActivated  = $groups['isActive'];
                unset($groups['isActive']);
            }

            foreach ($groups as $group => $data) {
                if (\is_array($data)) {
                    $this->groups[$group] = $data;
                }
            }

            $this->isNew         = false;
        } else {
            $this->isNew         = true;
        }
    }

    #[\Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[\Override]
    public function isNew(): bool
    {
        return $this->isNew;
    }

    #[\Override]
    public function isActivated(): bool
    {
        return $this->isActivated;
    }

    #[\Override]
    public function activate(): void
    {
        $this->isActivated      = true;
    }

    #[\Override]
    public function deactivate(): void
    {
        $this->isActivated      = false;
    }

    #[\Override]
    public function defineDescription(string $description): static
    {
        $this->description       = $description;

        return $this;
    }

    #[\Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[\Override] public function add(array   $bootloaders,
        array   $applications = [],
        array   $runtimeTags = [],
        array   $excludeTags = [],
        bool    $isActive = true,
        ?string $group = null
    ): static {
        $group ??= 'group-' . \count($this->groups);

        if (\array_key_exists($group, $this->groups)) {
            throw new \InvalidArgumentException('Group ' . $group . ' already exists');
        }

        $this->groups[$group]   = [
            'isActive'          => $isActive,
            'bootloader'        => $bootloaders,
            'forApplication'    => $applications,
            'runtimeTags'       => $runtimeTags,
            'excludeTags'       => $excludeTags,
        ];

        return $this;
    }

    #[\Override]
    public function deleteGroup(string $group): static
    {
        if (\array_key_exists($group, $this->groups)) {
            unset($this->groups[$group]);
        }

        return $this;
    }

    #[\Override]
    public function activateGroup(string $group): static
    {
        if (\array_key_exists($group, $this->groups)) {
            $this->groups[$group]['isActive'] = true;
        }

        return $this;
    }

    #[\Override]
    public function deactivateGroup(string $group): static
    {
        if (\array_key_exists($group, $this->groups)) {
            $this->groups[$group]['isActive'] = false;
        }

        return $this;
    }

    #[\Override]
    public function markAsSaved(): static
    {
        $this->isNew             = false;

        return $this;
    }

    #[\Override]
    public function getGroups(): array
    {
        return $this->groups;
    }
}
