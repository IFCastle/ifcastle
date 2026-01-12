<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

/**
 * data type that can be converted to JSON or vice versa.
 */
class TypeBasic extends TypeOneOf
{
    public function __construct(string $name, bool $isRequired = true, bool $isNullable = false)
    {
        parent::__construct($name, $isRequired, $isNullable);

        $this->describeCase(new TypeList('list', new self('item')))
            ->describeCase(new TypeNull('null'))
            ->describeCase(new TypeBool('boolean'))
            ->describeCase(new TypeString('string'))
            ->describeCase(new TypeInteger('number'))
            ->describeCase(new TypeFloat('float'));
    }
}
