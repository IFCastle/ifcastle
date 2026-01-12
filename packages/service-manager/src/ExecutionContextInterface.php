<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use IfCastle\TypeDefinitions\NativeSerialization\ArraySerializableInterface;

/**
 * ## ExecutionContextInterface.
 *
 * Provides access to the execution context.
 *
 * @template-extends \ArrayAccess<string, mixed>
 */
interface ExecutionContextInterface extends \ArrayAccess, ArraySerializableInterface {}
