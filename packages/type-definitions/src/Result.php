<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

final class Result implements ResultInterface
{
    private static ?Result $ok      = null;

    private static ?Result $err     = null;

    public static function ok(): self
    {
        if (self::$ok === null) {
            self::$ok               = new self();
        }

        return self::$ok;
    }

    public static function err(): self
    {
        if (self::$err === null) {
            self::$err              = new self(error: new \Exception('error'));
        }

        return self::$err;
    }

    public function __construct(public readonly mixed $result = null, public readonly ?\Throwable $error = null) {}

    #[\Override]
    public function getResult(): mixed
    {
        return $this->result;
    }

    #[\Override]
    public function getError(): ?\Throwable
    {
        return $this->error;
    }

    #[\Override]
    public function isOk(): bool
    {
        return $this->error === null;
    }

    #[\Override]
    public function isError(): bool
    {
        return $this->error !== null;
    }
}
