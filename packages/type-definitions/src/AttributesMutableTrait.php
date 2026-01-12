<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Exceptions\DescribeException;

trait AttributesMutableTrait
{
    use AttributesTrait;

    abstract protected function throwIfImmutable(): void;

    /**
     * @throws DescribeException
     */
    #[\Override]
    public function setAttributes(array $attributes): static
    {
        $this->throwIfImmutable();
        $this->attributes           = $attributes;
        return $this;
    }

    /**
     * @throws DescribeException
     */
    #[\Override]
    public function addAttributes(object ...$attributes): static
    {
        $this->throwIfImmutable();
        $this->attributes           = \array_merge($this->attributes, $attributes);
        return $this;
    }
}
