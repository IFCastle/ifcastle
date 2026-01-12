<?php

declare(strict_types=1);

namespace IfCastle\Protocol;

/**
 * Represents a request to the server as a flat list of parameters,
 * where each parameter has a unique name.
 */
interface RequestParametersInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getRequestParameters(): array;

    public function getRequestParameter(string $name): mixed;

    /**
     * Returns assoc array with parameters.
     * @return array<string, mixed>
     */
    public function requestParameters(string ...$names): array;

    /**
     * Returns an assoc array with parameters
     * If a parameter is not defined his value equal null.
     * @return array<string, mixed>
     */
    public function requestParametersWithNull(string ...$names): array;

    /**
     * Returns TRUE if all parameters defined
     * Null value counted as existed.
     */
    public function isRequestParametersExist(string ...$names): bool;

    /**
     * Returns TRUE if all parameters defined
     * Null value counted as undefined.
     */
    public function isRequestParametersDefined(string ...$names): bool;

    /**
     * Returns list of uploaded files.
     *
     * @return FileContainerInterface[]
     */
    public function getUploadedFiles(): array;


    public function getUploadedFile(string $name): ?FileContainerInterface;


    public function hasUploadedFile(string $name): bool;
}
