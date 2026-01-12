<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Exceptions\DecodingException;
use IfCastle\TypeDefinitions\Exceptions\RemoteException;
use IfCastle\TypeDefinitions\NativeSerialization\DataEncoder;

/**
 * ## TypeContainer.
 *
 * Object with information about type of data and data itself.
 * This container is used to transfer data between different systems when a type of data is unknown
 * or a type of data is variadic.
 *
 * We **don't recommend** using this container for **RPC calls between services**
 * because someone can use the wrong class name as a type of data, and it can be a security issue.
 *
 * TypeContainer is used in RPC calls between workers in the same node.
 *
 * ### Example
 * function foo(SomeInterface $data): void
 *
 */
class TypeContainer extends DefinitionAbstract
{
    #[\Override]
    public function isScalar(): bool
    {
        return false;
    }

    #[\Override]
    protected function validateValue(mixed $value): bool
    {
        return $value instanceof self;
    }

    #[\Override]
    public function encode(mixed $data): mixed
    {
        if ($data instanceof \Throwable && $data instanceof DefinitionStaticAwareInterface === false) {
            return [RemoteException::class, RemoteException::toArrayForRemote($data)];
        }

        return [$data::class, DataEncoder::dataEncode($data)];
    }

    /**
     * @throws DecodingException
     */
    #[\Override]
    public function decode(array|int|float|string|bool $data): mixed
    {
        if (\is_string($data)) {
            $data                   = $this->jsonDecode($data);
        }

        if (!\is_array($data)) {
            throw new DecodingException($this, 'Expected array', ['value' => \get_debug_type($data)]);
        }

        if (\count($data) !== 2) {
            throw new DecodingException($this, 'Expected array with two elements');
        }

        [$type, $decodedData]       = $data;

        if (false === \is_string($type)) {
            throw new DecodingException($this, 'Expected string as type', ['type' => \get_debug_type($type)]);
        }

        if (false === \class_exists($type)) {
            throw new DecodingException($this, 'Type class does not exist', ['type' => $type]);
        }

        if (false === \is_subclass_of($type, DefinitionStaticAwareInterface::class)) {
            throw new DecodingException(
                $this,
                'Type class should be instance of DefinitionStaticAwareInterface',
                ['type' => $type]
            );
        }

        return $type::definition()->decode($decodedData);
    }
}
