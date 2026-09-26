<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer\Http;

use IfCastle\Async\CancellationInterface;
use IfCastle\Async\ReadableStreamInterface;

/**
 * A readable stream over a fixed list of chunks; with $failure it throws that instead of ending.
 */
final class ChunkStream implements ReadableStreamInterface
{
    public bool $closed             = false;

    /**
     * @param list<string> $chunks
     */
    public function __construct(private array $chunks, private readonly ?\Throwable $failure = null) {}

    #[\Override]
    public function read(?CancellationInterface $cancellation = null): ?string
    {
        if ($this->chunks === [] && $this->failure !== null) {
            throw $this->failure;
        }

        return \array_shift($this->chunks);
    }

    #[\Override]
    public function isReadable(): bool
    {
        return $this->chunks !== [];
    }

    #[\Override]
    public function close(): void
    {
        $this->closed               = true;
    }

    #[\Override]
    public function isClosed(): bool
    {
        return $this->closed;
    }

    #[\Override]
    public function onClose(\Closure $onClose): void {}
}
