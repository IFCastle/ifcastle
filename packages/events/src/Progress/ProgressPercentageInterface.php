<?php

declare(strict_types=1);

namespace IfCastle\Events\Progress;

interface ProgressPercentageInterface extends ProgressInterface
{
    final public const string PERCENTAGE = 'percentage';

    final public const string DESCRIPTION = 'description';

    /**
     * Return number from 0 to 100.
     */
    public function getPercentage(): int;

    /**
     * Returns event description.
     */
    public function getDescription(): string;
}
