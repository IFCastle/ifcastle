<?php

declare(strict_types=1);

namespace IfCastle\Amphp;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\StreamException;
use Amp\Cancellation;
use IfCastle\Amphp\Internal\CancellationAdapter;
use IfCastle\Async\CancellationInterface;
use IfCastle\Async\ReadableStreamInterface;

readonly class ReadableStreamAdapter implements ReadableStreamInterface
{
    public function __construct(public ReadableStream $readableStream) {}

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

    /**
     * @throws StreamException
     */
    #[\Override]
    public function read(?CancellationInterface $cancellation = null): ?string
    {
        if ($cancellation !== null && false === $cancellation instanceof Cancellation) {
            $cancellation           = new CancellationAdapter($cancellation);
        }

        return $this->readableStream->read($cancellation);
    }

    #[\Override]
    public function isReadable(): bool
    {
        return $this->readableStream->isReadable();
    }
}
