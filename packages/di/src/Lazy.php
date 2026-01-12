<?php

declare(strict_types=1);

namespace IfCastle\DI;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class Lazy {}
