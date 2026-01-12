<?php

declare(strict_types=1);

namespace IfCastle\Protocol;

interface ProtocolInterface
{
    public function getProtocolName(): string;

    public function getProtocolVersion(): string;

    public function getProtocolRole(): string;
}
