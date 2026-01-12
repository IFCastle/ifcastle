<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class AsServiceMethod {}
