<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Value;

interface ContainerUnserializableInterface
{
    /**
     * @var string
     */
    public const string TYPE_NODE = '@';

    /**
     * @param array<mixed> $data
     */
    public function containerUnserialize(array $data): mixed;
}
