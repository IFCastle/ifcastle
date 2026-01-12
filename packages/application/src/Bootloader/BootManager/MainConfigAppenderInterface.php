<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader\BootManager;

/**
 * The interface defines a method that allows adding a new section to the application configuration,
 * provided it does not already exist.
 *
 * This interface is used for automatic configuration during component installation.
 */
interface MainConfigAppenderInterface
{
    /**
     * @param array<string, scalar|scalar[]|null> $data
     */
    public function appendSectionIfNotExists(string $section, array $data, string $comment = ''): void;
}
