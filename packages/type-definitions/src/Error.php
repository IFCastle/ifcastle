<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use Attribute;
use IfCastle\Exceptions\ClientException;
use IfCastle\TypeDefinitions\NativeSerialization\ArraySerializableInterface;
use IfCastle\TypeDefinitions\NativeSerialization\ArraySerializableValidatorInterface;
use IfCastle\TypeDefinitions\NativeSerialization\ArrayTyped;

#[Attribute(Attribute::TARGET_METHOD)]
readonly class Error implements ArraySerializableInterface
{
    final public const string TEMPLATE     = 't';

    final public const string DESCRIPTION  = 'd';

    final public const string PARAMETERS   = 'p';

    public string $errorClassName;

    /**
     * @var DefinitionInterface[]
     */
    public array $parameters;

    public function __construct(public string $template, public string $description = '', DefinitionInterface ...$parameters)
    {
        $this->parameters           = $parameters;
        $this->errorClassName       = ClientException::class;
    }

    #[\Override]
    public function toArray(?ArraySerializableValidatorInterface $validator = null): array
    {
        return [
            self::TEMPLATE          => $this->template,
            self::DESCRIPTION       => $this->description,
            self::PARAMETERS        => ArrayTyped::serializeList($validator, ...$this->parameters),
        ];
    }

    #[\Override]
    public static function fromArray(array $array, ?ArraySerializableValidatorInterface $validator = null): static
    {
        /* @phpstan-ignore-next-line */
        return new self(
            $array[self::TEMPLATE] ?? '',
            $array[self::DESCRIPTION] ?? '',
            /* @phpstan-ignore-next-line */
            ...ArrayTyped::unserializeList($validator, ...($array[self::PARAMETERS] ?? [])) ?? [],
        );
    }
}
