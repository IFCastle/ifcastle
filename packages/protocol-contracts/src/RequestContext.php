<?php

declare(strict_types=1);

namespace IfCastle\Protocol;

use IfCastle\DesignPatterns\KeyValueContext\KeyValueContext;

class RequestContext extends KeyValueContext implements RequestContextInterface
{
    #[\Override]
    public function getRemoteAddress(): ?string
    {
        return $this->context[self::REMOTE_ADDRESS] ?? null;
    }

    #[\Override]
    public function getRemotePort(): ?int
    {
        return $this->context[self::REMOTE_PORT] ?? null;
    }

    #[\Override]
    public function getRequestTime(): int
    {
        return $this->context[self::REQUEST_TIME] ?? 0;
    }
}
