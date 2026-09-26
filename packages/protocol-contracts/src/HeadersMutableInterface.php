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
     * Replaces every value of the header, whatever the case of its name; a list sets several.
     *
     * @param string|array<string> $value
     *
     * @return $this
     */
    public function setHeader(string $header, string|array $value): static;

    public function resetHeaders(): static;
}
