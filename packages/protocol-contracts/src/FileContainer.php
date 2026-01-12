<?php

declare(strict_types=1);

namespace IfCastle\Protocol;

use IfCastle\Async\ReadableStreamInterface;

class FileContainer implements FileContainerInterface
{
    public function __construct(
        protected readonly string $fileName,
        protected readonly ?string $mimeType    = null,
        protected readonly ?int $fileSize       = null,
        protected readonly ?\Throwable $error   = null
    ) {}


    #[\Override]
    public function getFileName(): string
    {
        return $this->fileName;
    }

    #[\Override]
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    #[\Override]
    public function getFileSize(): int
    {
        return $this->fileSize ?? 0;
    }

    #[\Override]
    public function getContents(): string
    {
        return '';
    }

    #[\Override]
    public function getStream(): ?ReadableStreamInterface
    {
        return null;
    }

    #[\Override]
    public function flushTo(string $fileName): static
    {
        return $this;
    }

    #[\Override]
    public function isEmpty(): bool
    {
        return $this->fileSize === 0;
    }

    #[\Override]
    public function isStream(): bool
    {
        return false;
    }

    #[\Override]
    public function getError(): ?\Throwable
    {
        return $this->error;
    }

    #[\Override]
    public function dispose(): void {}
}
