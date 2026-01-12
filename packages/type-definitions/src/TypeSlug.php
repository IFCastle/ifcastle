<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

class TypeSlug extends TypeString
{
    protected string|null $pattern  = '/^[A-Z][A-Z_0-9-]+$/i';

    protected string|null $ecmaPattern = '[A-Za-z][A-Za-z_0-9-]+';
}
