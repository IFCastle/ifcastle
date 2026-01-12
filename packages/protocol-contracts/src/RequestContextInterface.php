<?php

declare(strict_types=1);

namespace IfCastle\Protocol;

/**
 * @template-extends \ArrayAccess<string, scalar|scalar[]|null>
 * @template-extends \IteratorAggregate<string, scalar|scalar[]|null>
 */
interface RequestContextInterface extends \ArrayAccess, \IteratorAggregate
{
    public const string REMOTE_ADDRESS  = 'address';

    public const string REMOTE_PORT     = 'port';

    public const string REQUEST_TIME    = 'time';

    public function getRemoteAddress(): ?string;

    public function getRemotePort(): ?int;

    public function getRequestTime(): int;
}
