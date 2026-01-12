<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\CodeGenerator;

interface SelfGeneratorInterface
{
    public function generateEncodeCode(): string;

    public function generateDecodeCode(): string;

    public function generateValidationCode(): string;
}
