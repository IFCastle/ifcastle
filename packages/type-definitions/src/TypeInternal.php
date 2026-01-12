<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\Exceptions\UnexpectedMethodMode;

final class TypeInternal extends DefinitionAbstract
{
    #[\Override]
    protected function validateValue(mixed $value): bool
    {
        return $value instanceof $this->type;
    }

    #[\Override]
    public function isScalar(): bool
    {
        return false;
    }

    /**
     * @throws UnexpectedMethodMode
     */
    #[\Override]
    public function encode(mixed $data): mixed
    {
        throw new UnexpectedMethodMode(__METHOD__);
    }

    /**
     * @throws UnexpectedMethodMode
     */
    #[\Override]
    public function decode(float|array|bool|int|string $data): mixed
    {
        throw new UnexpectedMethodMode(__METHOD__);
    }
}
