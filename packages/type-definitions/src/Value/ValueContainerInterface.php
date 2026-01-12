<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Value;

use IfCastle\TypeDefinitions\DefinitionInterface;
use IfCastle\TypeDefinitions\DefinitionStaticAwareInterface;

/**
 * ## ValueContainerI
 * It's a basic interface for Value Container.
 *
 */
interface ValueContainerInterface extends DefinitionStaticAwareInterface, InstantiateInterface, ContainerSerializableInterface
{
    public function getDefinition(): DefinitionInterface;

    public function getValue(): mixed;
}
