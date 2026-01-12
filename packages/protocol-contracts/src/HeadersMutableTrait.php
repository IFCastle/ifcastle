<?php

declare(strict_types=1);

namespace IfCastle\Protocol;

use IfCastle\DesignPatterns\Immutable\ImmutableTrait;
use IfCastle\Exceptions\LogicalException;

trait HeadersMutableTrait
{
    use ImmutableTrait;
    use HeadersTrait;

    /**
     * @throws LogicalException
     */
    public function setHeaders(array $headers): static
    {
        $this->throwIfImmutable();

        foreach ($headers as $key => $header) {
            $this->setHeader($key, $header);
        }

        return $this;
    }

    /**
     * @throws LogicalException
     */
    public function setHeader(string $header, string|array $value): static
    {
        $this->throwIfImmutable();

        /* @phpstan-ignore-next-line */
        if (\array_key_exists($header, $this->headers) && !\is_array($this->headers[$header])) {
            $this->headers[$header] = [$this->headers[$header]];
        }

        if (!\is_array($value)) {
            $this->headers[$header][] = $value;
        } else {
            $this->headers[$header] = \array_merge($this->headers[$header], $value);
        }

        return $this;
    }

    /**
     * @throws LogicalException
     */
    public function resetHeaders(): static
    {
        $this->throwIfImmutable();

        $this->headers              = [];
        return $this;
    }
}
