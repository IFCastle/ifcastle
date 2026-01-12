<?php

declare(strict_types=1);

namespace IfCastle\Events\Progress;

use IfCastle\TypeDefinitions\ResultInterface;

interface ProgressItemInterface extends ProgressInterface
{
    /**
     * @var string
     */
    final public const string ITEM_CURRENT = 'item_current';

    /**
     * @var string
     */
    final public const string ITEM_TOTAL = 'item_total';

    /**
     * @var string
     */
    final public const string ITEM_NAME = 'item_name';

    final public const string RESULT = 'result';

    /**
     * Return progress item name.
     */
    public function getProgressItemName(): string;

    public function getProgressItemCurrent(): int;

    public function getProgressItemTotal(): int;

    public function getProgressItemResult(): ?ResultInterface;
}
