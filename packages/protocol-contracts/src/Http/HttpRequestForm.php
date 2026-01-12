<?php

declare(strict_types=1);

namespace IfCastle\Protocol\Http;

final readonly class HttpRequestForm
{
    /**
     * @param array<string, mixed> $get
     * @param array<string, mixed> $post
     * @param array<string, mixed> $files
     */
    public function __construct(
        public array $get           = [],
        public array $post          = [],
        public array $files         = [],
    ) {}
}
