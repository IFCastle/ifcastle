<?php

declare(strict_types=1);

namespace IfCastle\Console;

use IfCastle\DI\ContainerInterface;
use IfCastle\ServiceManager\DescriptorRepositoryInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SymfonyApplication extends \Symfony\Component\Console\Application
{
    private readonly CommandLoader $commandLoader;

    #[\Override]
    public function run(?InputInterface $input = null, ?OutputInterface $output = null): int
    {
        try {
            return parent::run($input, $output);
        } catch (\Throwable $throwable) {

            /**
             * Temporary huck for Throwable exceptions.
             */

            if (null === $input) {
                $input              = new ArgvInput();
            }

            if (null === $output) {
                $output             = new ConsoleOutput();
            }

            if ($output instanceof ConsoleOutputInterface) {
                $this->renderThrowable($throwable, $output->getErrorOutput());
            } else {
                $this->renderThrowable($throwable, $output);
            }

            $exitCode               = $throwable->getCode();

            if (\is_numeric($exitCode)) {
                $exitCode = (int) $exitCode;
                if ($exitCode <= 0) {
                    $exitCode = 1;
                }
            } else {
                $exitCode = 1;
            }

            return $exitCode;
        }
    }

    public function __construct(
        ContainerInterface            $container,
        DescriptorRepositoryInterface $descriptorRepository,
        string                        $name = 'IfCastle',
        string                        $version = '1.0.0'
    ) {
        parent::__construct($name, $version);
        $this->commandLoader        = new CommandLoader($container, $descriptorRepository);
        $this->setCommandLoader($this->commandLoader);
    }
}
