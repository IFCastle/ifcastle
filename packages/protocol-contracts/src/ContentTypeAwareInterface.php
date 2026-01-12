<?php

declare(strict_types=1);

namespace IfCastle\Protocol;

interface ContentTypeAwareInterface
{
    public function getContentType(): string|null;
}
