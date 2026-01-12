<?php

declare(strict_types=1);

namespace IfCastle\Application\WorkerProtocol;

use IfCastle\Protocol\HeadersMutableInterface;
use IfCastle\Protocol\RequestInterface;
use IfCastle\Protocol\RequestParametersInterface;
use IfCastle\ServiceManager\CommandDescriptorInterface;
use IfCastle\ServiceManager\ExecutionContextInterface;

interface WorkerRequestInterface extends RequestInterface, RequestParametersInterface, HeadersMutableInterface
{
    public const string REQUEST_HEADERS = ':h';

    public function getCommandDescriptor(): CommandDescriptorInterface;

    public function getExecutionContext(): ExecutionContextInterface;
}
