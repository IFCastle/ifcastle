<?php

declare(strict_types=1);

namespace IfCastle\Protocol;

interface RequestParametersMutableInterface extends RequestParametersInterface, \IfCastle\DesignPatterns\Immutable\ImmutableInterface
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function setRequestParameters(array $parameters): void;

    /**
     * @param array<string, mixed> $parameters
     */
    public function mergeRequestParameter(array $parameters): void;

    /**
     * @param array<FileContainerInterface> $files
     */
    public function setUploadedFiles(array $files): void;
}
