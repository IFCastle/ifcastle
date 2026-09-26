<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer\Http;

use IfCastle\Async\ReadableStreamInterface;
use IfCastle\Protocol\FileContainerInterface;
use TrueAsync\UploadedFile;

/**
 * A file uploaded through TrueAsync\HttpServer, kept by the server in a temporary file.
 */
final readonly class UploadedFileContainer implements FileContainerInterface
{
    /**
     * Converts the server's file tree, where a field sent as a list (photos[]) holds a list of files,
     * keeping its shape.
     *
     * @param array<string|int, UploadedFile|array<mixed>> $files
     *
     * @return array<string|int, FileContainerInterface|array<mixed>>
     */
    public static function fromFiles(array $files): array
    {
        $result                     = [];

        foreach ($files as $name => $file) {
            $result[$name]          = $file instanceof UploadedFile ? new self($file) : self::fromFiles($file);
        }

        return $result;
    }

    public function __construct(private UploadedFile $file) {}

    #[\Override]
    public function getFileName(): string
    {
        return $this->file->getClientFilename() ?? '';
    }

    #[\Override]
    public function getMimeType(): ?string
    {
        return $this->file->getClientMediaType();
    }

    #[\Override]
    public function getFileSize(): int
    {
        return $this->file->getSize() ?? 0;
    }

    /**
     * @throws \RuntimeException when the file has been moved by flushTo()
     */
    #[\Override]
    public function getContents(): string
    {
        $stream                     = $this->file->getStream();

        if (false === \is_resource($stream)) {
            return '';
        }

        \rewind($stream);

        return (string) \stream_get_contents($stream);
    }

    #[\Override]
    public function getStream(): ?ReadableStreamInterface
    {
        return null;
    }

    /**
     * Moves the file to $fileName; afterwards getContents() fails.
     *
     * @throws \RuntimeException when the file has been moved already or cannot be written
     */
    #[\Override]
    public function flushTo(string $fileName): static
    {
        $this->file->moveTo($fileName);

        return $this;
    }

    #[\Override]
    public function isEmpty(): bool
    {
        return $this->getFileSize() === 0;
    }

    #[\Override]
    public function isStream(): bool
    {
        return false;
    }

    #[\Override]
    public function getError(): ?\Throwable
    {
        $code                       = $this->file->getError();

        return $code === \UPLOAD_ERR_OK ? null : new \RuntimeException('File upload failed with UPLOAD_ERR code ' . $code);
    }

    /**
     * Holds no resource of its own: the temporary file belongs to the server.
     */
    #[\Override]
    public function dispose(): void {}
}
