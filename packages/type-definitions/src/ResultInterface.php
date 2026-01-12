<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

/**
 * Basic container for Result like RUST.
 */
interface ResultInterface
{
    public function getResult(): mixed;

    public function getError(): ?\Throwable;

    public function isOk(): bool;

    public function isError(): bool;
}
