<?php

declare(strict_types=1);

namespace IfCastle\Protocol;

interface RequestHandlerInterface
{
    public function handleRequest(RequestInterface $request, ResponseFactoryInterface $responseFactory): ResponseInterface;
}
