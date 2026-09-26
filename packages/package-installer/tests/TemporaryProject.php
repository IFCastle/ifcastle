<?php

declare(strict_types=1);

namespace IfCastle\PackageInstaller;

use IfCastle\Configurator\Configurator;

/**
 * A project directory in the system temp directory that the installer application can boot from:
 * vendor/, an empty main.ini and the INI configurator as its only bootloader.
 */
final readonly class TemporaryProject
{
    public string $dir;

    public function __construct()
    {
        $this->dir                  = \sys_get_temp_dir() . '/ifcastle-installer-' . \bin2hex(\random_bytes(6));

        // ApplicationAbstract refuses to start without a vendor directory.
        \mkdir($this->dir . '/vendor', 0o777, true);
        \mkdir($this->dir . '/bootloader');
        \file_put_contents($this->dir . '/main.ini', '');
        \file_put_contents(
            $this->dir . '/bootloader/configurator.ini',
            "isActive = true\n\n[group-0]\nisActive = true\nbootloader[] = \"" . \addslashes(Configurator::class) . "\"\n"
        );
    }

    public function remove(): void
    {
        $this->removeDir($this->dir);
    }

    private function removeDir(string $dir): void
    {
        foreach (\scandir($dir) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path                   = $dir . '/' . $entry;
            \is_dir($path) ? $this->removeDir($path) : \unlink($path);
        }

        \rmdir($dir);
    }
}
