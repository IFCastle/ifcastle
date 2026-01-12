<?php

declare(strict_types=1);

namespace IfCastle\DI\Exceptions;

final class MaxResolutionDepthException extends \Exception
{
    /**
     * @param array<string> $resolvingKeys
     */
    public function __construct(int $depth, array $resolvingKeys)
    {
        parent::__construct("Max resolution depth of $depth reached: "
                            . \implode(' -> ', $resolvingKeys)
        );
    }
}
