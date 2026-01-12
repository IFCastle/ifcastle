<?php

declare(strict_types=1);

namespace IfCastle\Protocol;

use IfCastle\Async\ReadableStreamInterface;
use IfCastle\DI\DisposableInterface;

interface FileContainerInterface extends DisposableInterface
{
    public function getFileName(): string;

    public function getMimeType(): ?string;

    public function getFileSize(): int;

    public function getContents(): string;

    public function getStream(): ?ReadableStreamInterface;

    public function flushTo(string $fileName): static;

    public function isEmpty(): bool;

    public function isStream(): bool;

    public function getError(): ?\Throwable;
}
