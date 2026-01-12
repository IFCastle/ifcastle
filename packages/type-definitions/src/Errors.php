<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

class Errors extends TypeOneOf
{
    /**
     * @var Error[]
     */
    protected array $errors;

    public function __construct(string $componentName, Error ...$errors)
    {
        parent::__construct($componentName);

        $this->errors               = $errors;
    }

    #[\Override]
    protected function defineEnumCases(): void
    {
        foreach ($this->errors as $error) {
            $class                  = $error->errorClassName;
            $this->describeCase(\call_user_func($class . '::definitionByAttribute', $error));
        }
    }
}
