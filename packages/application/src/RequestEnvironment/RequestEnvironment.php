<?php

declare(strict_types=1);

namespace IfCastle\Application\RequestEnvironment;

use IfCastle\Application\Environment\SystemEnvironmentInterface;
use IfCastle\DesignPatterns\FinalHandlers\FinalHandlersInterface;
use IfCastle\DesignPatterns\FinalHandlers\FinalHandlersTrait;
use IfCastle\DI\Container;
use IfCastle\DI\ContainerMutableTrait;
use IfCastle\DI\Resolver;
use IfCastle\Exceptions\CompositeException;
use IfCastle\Protocol\RequestInterface;
use IfCastle\Protocol\ResponseFactoryInterface;
use IfCastle\Protocol\ResponseInterface;

class RequestEnvironment extends Container implements RequestEnvironmentInterface, FinalHandlersInterface
{
    use ContainerMutableTrait;
    use FinalHandlersTrait;

    public function __construct(protected object|null $originalRequest = null, ?SystemEnvironmentInterface $parentContainer = null)
    {
        parent::__construct(new Resolver(), [
            RequestInterface::class             => null,
            ResponseFactoryInterface::class     => null,
            ResponseInterface::class            => null,
        ], $parentContainer, true);

        // define self-reference as RequestEnvironmentInterface
        if (false === \array_key_exists(RequestEnvironmentInterface::class, $this->container)) {
            $this->container[RequestEnvironmentInterface::class] = \WeakReference::create($this);
        }
    }

    #[\Override]
    public function getSystemEnvironment(): SystemEnvironmentInterface
    {
        $parent                     = $this->getParentContainer();

        if ($parent instanceof SystemEnvironmentInterface) {
            return $parent;
        }

        throw new \RuntimeException('SystemEnvironment not found in parent containers');
    }

    #[\Override]
    public function originalRequest(): object|null
    {
        return $this->originalRequest;
    }

    #[\Override]
    public function dispose(): void
    {
        $errors                     = $this->executeFinalHandlers();

        $this->originalRequest      = null;

        if (\array_key_exists(ResponseInterface::class, $this->container)) {
            unset($this->container[ResponseInterface::class]);
        }

        parent::dispose();

        if (\count($errors) === 1) {
            throw $errors[0];
        }

        if (\count($errors) > 1) {
            throw new CompositeException('Multiple exceptions occurred while RequestEnvironment disposed', ...$errors);
        }
    }

    #[\Override]
    public function getRequest(): RequestInterface
    {
        return $this->resolveDependency(RequestInterface::class);
    }

    #[\Override]
    public function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->resolveDependency(ResponseFactoryInterface::class);
    }

    #[\Override]
    public function getResponse(): ResponseInterface|null
    {
        return $this->findDependency(ResponseInterface::class);
    }

    #[\Override]
    public function defineResponse(ResponseInterface $response): void
    {
        $this->container[ResponseInterface::class] = $response;
    }

    #[\Override]
    public function redefineResponse(ResponseInterface $response): void
    {
        $this->container[ResponseInterface::class] = $response;
    }
}
