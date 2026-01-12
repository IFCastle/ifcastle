<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use Attribute;
use IfCastle\TypeDefinitions\NativeSerialization\AttributeNameInterface;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class ServiceTagsExclude implements AttributeNameInterface
{
    /**
     * @var  string[]
     */
    public array $tags;

    public function __construct(string ...$tags)
    {
        $this->tags = $tags;
    }

    #[\Override]
    public function getAttributeName(): string
    {
        return self::class;
    }
}
