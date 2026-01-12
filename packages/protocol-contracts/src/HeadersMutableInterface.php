<?php

declare(strict_types=1);

namespace IfCastle\Protocol;

interface HeadersMutableInterface extends HeadersInterface, \IfCastle\DesignPatterns\Immutable\ImmutableInterface
{
    /**
     * @param array<string, array<string>> $headers
     *
     * @return $this
     */
    public function setHeaders(array $headers): static;

    /**
     * @param string|array<string> $value
     *
     * @return $this
     */
    public function setHeader(string $header, string|array $value): static;

    public function resetHeaders(): static;
}
