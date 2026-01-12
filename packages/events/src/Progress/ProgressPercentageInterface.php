<?php

declare(strict_types=1);

namespace IfCastle\Events\Progress;

interface ProgressPercentageInterface extends ProgressInterface
{
    /**
     * @var string
     */
    final public const string PERCENTAGE = 'percentage';

    /**
     * @var string
     */
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
