<?php

declare(strict_types=1);

namespace IfCastle\Protocol;

interface ResponseFactoryInterface
{
    public function createResponse(?string $protocolName = null, ?string $protocolVersion = null, ?string $protocolRole = null): ResponseInterface;
}
