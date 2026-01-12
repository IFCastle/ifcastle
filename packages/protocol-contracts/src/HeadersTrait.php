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
        return \array_key_exists($name, $this->headers);
    }

    public function getHeader(string $name): array
    {
        return $this->headers[$name] ?? [];
    }

    public function getHeaderLine(string $name): string
    {
        if (\array_key_exists($name, $this->headers)) {
            return \implode(',', $this->headers[$name]);
        }

        return '';
    }
}
