<?php

declare(strict_types=1);

namespace IfCastle\Console;

use IfCastle\Application\ApplicationAbstract;
use IfCastle\Application\EngineRolesEnum;
use IfCastle\ServiceManager\DescriptorRepositoryInterface;

class ConsoleApplication extends ApplicationAbstract
{
    /**
     * @throws \Exception
     */
    #[\Override]
    protected function engineStartAfter(): void
    {
        try {
            $application                = new SymfonyApplication(
                $this->systemEnvironment,
                $this->systemEnvironment->resolveDependency(DescriptorRepositoryInterface::class)
            );
        } catch (\Throwable $throwable) {
            echo 'Console start error: ' . $throwable->getMessage() . PHP_EOL;
            \print_r($throwable);
            echo PHP_EOL;
            exit(-2);
        }

        $application->run();
    }

    #[\Override]
    protected function defineEngineRole(): EngineRolesEnum
    {
        return EngineRolesEnum::CONSOLE;
    }
}
