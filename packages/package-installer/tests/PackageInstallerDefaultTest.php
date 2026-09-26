<?php

declare(strict_types=1);

namespace IfCastle\PackageInstaller;

use IfCastle\Application\Bootloader\BootManager\BootManagerByDirectory;
use IfCastle\Application\Bootloader\BootManager\BootManagerInterface;
use IfCastle\Configurator\ServiceConfig;
use IfCastle\OsUtilities\Safe;
use PHPUnit\Framework\TestCase;

class PackageInstallerDefaultTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        $this->cleanDir();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->cleanDir();
    }

    public function testInstall(): void
    {
        $bootManager                = $this->instanciateBootManager();
        $zeroContext                = new ZeroContext(__DIR__);
        $deferredTasks              = $this->createStub(DeferredTasksInterface::class);
        $packageInstaller           = new PackageInstallerDefault($bootManager, $zeroContext, $deferredTasks);

        $packageInstaller->setConfig([
            PackageInstallerInterface::PACKAGE  => [
                PackageInstallerInterface::NAME => 'testPackage',
                PackageInstallerInterface::BOOTLOADERS => [
                    'testBootloader',
                ],
            ],
        ], 'test-package');

        $packageInstaller->install();

        $this->assertFileExists(__DIR__ . '/bootloader/testPackage.ini');

        $data                       = \parse_ini_file(__DIR__ . '/bootloader/testPackage.ini', true, \INI_SCANNER_TYPED);

        $this->assertArrayHasKey('isActive', $data);
        $this->assertTrue($data['isActive']);
        $this->assertArrayHasKey('group-0', $data);
        $this->assertEquals(['isActive' => true, 'bootloader' => ['testBootloader']], $data['group-0']);
    }

    public function testUpdate(): void
    {
        $bootManager                = $this->instanciateBootManager();
        $zeroContext                = new ZeroContext(__DIR__);
        $deferredTasks              = $this->createStub(DeferredTasksInterface::class);
        $packageInstaller           = new PackageInstallerDefault($bootManager, $zeroContext, $deferredTasks);

        $packageInstaller->setConfig([
            PackageInstallerInterface::PACKAGE  => [
                PackageInstallerInterface::NAME => 'testPackage',
                PackageInstallerInterface::BOOTLOADERS => [
                    'testBootloader',
                ],
            ],
        ], 'test-package');

        $packageInstaller->install();

        $this->assertFileExists(__DIR__ . '/bootloader/testPackage.ini');

        $data                       = \parse_ini_file(__DIR__ . '/bootloader/testPackage.ini', true, \INI_SCANNER_TYPED);

        $this->assertArrayHasKey('isActive', $data);
        $this->assertTrue($data['isActive']);
        $this->assertArrayHasKey('group-0', $data);
        $this->assertEquals(['isActive' => true, 'bootloader' => ['testBootloader']], $data['group-0']);

        $packageInstaller->setConfig([
            PackageInstallerInterface::PACKAGE  => [
                PackageInstallerInterface::NAME => 'testPackage',
                PackageInstallerInterface::BOOTLOADERS => [
                    'testBootloader',
                    'testBootloader2',
                ],
            ],
        ], 'test-package');

        $packageInstaller->update();

        $this->assertFileExists(__DIR__ . '/bootloader/testPackage.ini');

        $data                       = \parse_ini_file(__DIR__ . '/bootloader/testPackage.ini', true, \INI_SCANNER_TYPED);

        $this->assertArrayHasKey('isActive', $data);
        $this->assertTrue($data['isActive']);
        $this->assertArrayHasKey('group-0', $data);
        $this->assertEquals(['isActive' => true, 'bootloader' => ['testBootloader', 'testBootloader2']], $data['group-0']);
    }

    /**
     * A package that gains an installer section in a later version reaches the installer through
     * update(), with no component installed before it.
     */
    public function testUpdateInstallsAPackageItDidNotKnow(): void
    {
        $packageInstaller           = new PackageInstallerDefault(
            $this->instanciateBootManager(), new ZeroContext(__DIR__), $this->createStub(DeferredTasksInterface::class)
        );

        $packageInstaller->setConfig([
            PackageInstallerInterface::PACKAGE  => [
                PackageInstallerInterface::NAME => 'testPackage',
                PackageInstallerInterface::BOOTLOADERS => ['testBootloader'],
            ],
        ], 'test-package');

        $packageInstaller->update();

        $data                       = \parse_ini_file(__DIR__ . '/bootloader/testPackage.ini', true, \INI_SCANNER_TYPED);

        $this->assertEquals(['isActive' => true, 'bootloader' => ['testBootloader']], $data['group-0'] ?? null);
    }

    public function testInstallWritesServices(): void
    {
        $project                    = new TemporaryProject();

        try {
            $packageInstaller       = new PackageInstallerDefault(
                new BootManagerByDirectory($project->dir . '/bootloader'),
                new ZeroContext($project->dir),
                $this->createStub(DeferredTasksInterface::class)
            );

            $packageInstaller->setConfig([
                PackageInstallerInterface::PACKAGE  => [
                    PackageInstallerInterface::NAME => 'testPackage',
                    PackageInstallerInterface::BOOTLOADERS => ['testBootloader'],
                    // Keeps the missing bootloader class out of the installer application.
                    PackageInstallerInterface::APPLICATIONS => ['server'],
                ],
                PackageInstallerInterface::SERVICES => [[
                    Service::NAME       => 'testService',
                    Service::CLASS_NAME => 'TestService',
                    Service::IS_ACTIVE  => false,
                ]],
            ], 'test-package');

            $packageInstaller->install();

            $service                = new ServiceConfig($project->dir)->getServiceCollection('testService')['testService'][0] ?? [];

            $this->assertSame(
                ['TestService', false, 'testPackage'],
                [$service['class'] ?? null, $service['isActive'] ?? null, $service['package'] ?? null]
            );
        } finally {
            $project->remove();
        }
    }

    private function instanciateBootManager(): BootManagerInterface
    {
        $bootloaderDir              = __DIR__ . '/bootloader';

        if (!\is_dir($bootloaderDir)) {
            Safe::execute(fn() => \mkdir($bootloaderDir));
        }

        if (!\is_dir($bootloaderDir)) {
            throw new \RuntimeException('Bootloader directory is not exist: ' . $bootloaderDir);
        }

        return new BootManagerByDirectory($bootloaderDir);
    }

    private function cleanDir(): void
    {
        $bootloaderDir              = __DIR__ . '/bootloader';

        if (!\is_dir($bootloaderDir)) {
            return;
        }

        foreach (\scandir($bootloaderDir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            \unlink($bootloaderDir . '/' . $file);
        }
    }
}
