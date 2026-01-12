<?php

declare(strict_types=1);

namespace IfCastle\Application\RequestEnvironment;

use IfCastle\Application\Environment\SystemEnvironmentInterface;
use IfCastle\DI\ContainerMutableInterface;
use IfCastle\Protocol\RequestInterface;
use IfCastle\Protocol\ResponseFactoryInterface;
use IfCastle\Protocol\ResponseInterface;

interface RequestEnvironmentInterface extends ContainerMutableInterface
{
    public const string ORIGINAL_REQUEST = 'originalRequest';

    public function getSystemEnvironment(): SystemEnvironmentInterface;

    public function originalRequest(): object|null;

    public function getRequest(): RequestInterface;

    public function getResponseFactory(): ResponseFactoryInterface;

    public function getResponse(): ResponseInterface|null;

    public function defineResponse(ResponseInterface $response): void;

    public function redefineResponse(ResponseInterface $response): void;
}
