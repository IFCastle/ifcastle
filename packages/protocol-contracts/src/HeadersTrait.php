<?php

declare(strict_types=1);

namespace IfCastle\Protocol;

trait HeadersTrait
{
    /**
     * @var array<string, array<string>>
     */
    protected array $headers        = [];

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        return $this->findHeaderName($name) !== null;
    }

    public function getHeader(string $name): array
    {
        $headerName                 = $this->findHeaderName($name);

        return $headerName === null ? [] : $this->headers[$headerName];
    }

    public function getHeaderLine(string $name): string
    {
        return \implode(',', $this->getHeader($name));
    }

    /**
     * The stored spelling of the header $name, compared case-insensitively; null when absent.
     */
    protected function findHeaderName(string $name): ?string
    {
        if (\array_key_exists($name, $this->headers)) {
            return $name;
        }

        foreach (\array_keys($this->headers) as $headerName) {
            if (\strcasecmp($headerName, $name) === 0) {
                return $headerName;
            }
        }

        return null;
    }
}
