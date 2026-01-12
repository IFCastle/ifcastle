<?php

declare(strict_types=1);

namespace IfCastle\Amphp;

use Amp\ByteStream\ReadableStream;
use Amp\Cancellation;
use IfCastle\Async\ReadableStreamInterface;

final readonly class AmphpReadableStreamAdapter implements ReadableStream
{
    public function __construct(public ReadableStreamInterface $readableStream) {}

    #[\Override]
    public function close(): void
    {
        $this->readableStream->close();
    }

    #[\Override]
    public function isClosed(): bool
    {
        return $this->readableStream->isClosed();
    }

    #[\Override]
    public function onClose(\Closure $onClose): void
    {
        $this->readableStream->onClose($onClose);
    }

    #[\Override]
    public function read(?Cancellation $cancellation = null): ?string
    {
        return $this->readableStream->read(new CancellationExternalAdapter($cancellation));
    }

    #[\Override]
    public function isReadable(): bool
    {
        return $this->readableStream->isReadable();
    }
}
