<?php

declare(strict_types=1);

namespace IfCastle\Mcp;

/**
 * The behaviour hints of a tool definition. Every hint is always sent, since the specification's defaults
 * (destructive and open-world) suit few tools.
 */
final readonly class ToolAnnotations
{
    public function __construct(
        public bool $readOnly = false,
        public bool $destructive = true,
        public bool $idempotent = false,
        public bool $openWorld = true,
    ) {}

    /** A tool that only reads the server's own data. */
    public static function read(): self
    {
        return new self(readOnly: true, destructive: false, idempotent: true, openWorld: false);
    }

    /** A tool that changes the server's own data and is not idempotent. */
    public static function write(bool $destructive = false): self
    {
        return new self(readOnly: false, destructive: $destructive, idempotent: false, openWorld: false);
    }

    /**
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        return ['readOnlyHint' => $this->readOnly, 'destructiveHint' => $this->destructive,
            'idempotentHint' => $this->idempotent, 'openWorldHint' => $this->openWorld];
    }
}
