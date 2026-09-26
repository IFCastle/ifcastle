<?php

declare(strict_types=1);

namespace IfCastle\PackageInstaller;

use PHPUnit\Framework\TestCase;

class DeferredTaskExecutorTest extends TestCase
{
    private TemporaryProject $project;

    #[\Override]
    protected function setUp(): void
    {
        $this->project              = new TemporaryProject();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->project->remove();
    }

    public function testMainConfigReachesMainIni(): void
    {
        $tasksFile                  = $this->project->dir . '/tasks.json';

        \file_put_contents($tasksFile, \json_encode(['tasks' => [[
            'description'           => 'Applying main config for package: web_server',
            'taskData'              => [
                'type'              => 'main-config',
                'packageName'       => 'web_server',
                'data'              => [
                    'server'        => [
                        PackageInstallerInterface::COMMENT => 'Host and port to listen on.',
                        PackageInstallerInterface::CONFIG  => ['host' => '127.0.0.1', 'port' => 9095],
                    ],
                ],
            ],
        ]]]));

        $this->expectOutputString("Applying: Applying main config for package: web_server\n");

        new DeferredTaskExecutor($this->project->dir)->executeFromFile($tasksFile);

        $mainConfig                 = \parse_ini_file($this->project->dir . '/main.ini', true, \INI_SCANNER_TYPED);

        $this->assertSame(['host' => '127.0.0.1', 'port' => 9095], $mainConfig['server'] ?? null);
    }
}
