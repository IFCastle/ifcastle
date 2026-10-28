<?php

declare(strict_types=1);

namespace IfCastle\Mcp;

/** The tools a server offers, in the order they were added, which is the order of `tools/list`. */
final class ToolRegistry
{
    /** @var array<string, Tool> */
    private array $tools        = [];

    /**
     * @throws \InvalidArgumentException when a tool of the same name is already registered
     */
    public function add(Tool $tool): self
    {
        if (isset($this->tools[$tool->name])) {
            throw new \InvalidArgumentException("Tool $tool->name is already registered");
        }

        $this->tools[$tool->name] = $tool;

        return $this;
    }

    public function find(string $name): ?Tool
    {
        return $this->tools[$name] ?? null;
    }

    /**
     * @return list<Tool> the tools listed for $principal
     */
    public function listedFor(object $principal): array
    {
        return \array_values(\array_filter($this->tools,
            static fn(Tool $tool): bool => $tool->isListedFor($principal)));
    }
}
