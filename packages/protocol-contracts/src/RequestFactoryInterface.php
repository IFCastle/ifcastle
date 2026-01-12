<?php

declare(strict_types=1);

namespace IfCastle\Protocol;

interface RequestFactoryInterface
{
    public function createRequest(): RequestInterface;
}
