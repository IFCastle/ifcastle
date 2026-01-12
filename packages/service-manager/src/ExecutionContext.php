<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use IfCastle\DesignPatterns\KeyValueContext\KeyValueContext;
use IfCastle\Exceptions\SerializeException;
use IfCastle\Exceptions\UnSerializeException;
use IfCastle\TypeDefinitions\NativeSerialization\ArraySerializableTrait;
use IfCastle\TypeDefinitions\NativeSerialization\ArraySerializableValidatorInterface;

class ExecutionContext extends KeyValueContext implements ExecutionContextInterface
{
    use ArraySerializableTrait;

    /**
     * @throws SerializeException
     */
    #[\Override]
    public function toArray(?ArraySerializableValidatorInterface $validator = null): array
    {
        if ($validator?->isSerializationAllowed($this) === false) {
            throw new SerializeException('Serialization is not allowed', $this, 'array', $this);
        }

        return $this->serializeToArray($this->context, $validator);
    }

    /**
     * @throws UnSerializeException
     */
    #[\Override]
    public static function fromArray(array $array, ?ArraySerializableValidatorInterface $validator = null): static
    {
        return new static(static::unserializeFromArray($array));
    }
}
