<?php

declare(strict_types=1);

namespace IfCastle\DI\Exceptions;

use IfCastle\DI\ContainerInterface;
use IfCastle\DI\DependencyInterface;
use IfCastle\DI\DescriptorInterface;

class DependencyNotFound extends \Exception
{
    public function __construct(string|DescriptorInterface $name,
        ContainerInterface $container,
        ?DependencyInterface $forDependency = null,
        int $stackOffset = 4,
        ?\Throwable $previous = null
    ) {

        parent::__construct($this->generateMessage($name, $container, $forDependency, $stackOffset), 0, $previous);
    }

    protected function generateMessage(string|DescriptorInterface $name, ContainerInterface $container, ?DependencyInterface $forDependency = null, int $stackOffset = 4): string
    {
        $requiredBy                 = '';
        $file                       = '';
        $line                       = '';
        $key                        = $name instanceof DescriptorInterface ? $name->getDependencyKey() : $name;
        $forDependency              = $forDependency !== null ? $forDependency->getDependencyName() : '';
        $container                  = $container->getContainerLabel();

        $backtrace                  = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $stackOffset + 1);
        $withFactory                = '';

        if ($name instanceof DescriptorInterface && $name->getProvider() !== null) {
            $withFactory            = ' with Factory: \'' . \get_debug_type($name->getProvider()) . '\'';
        }

        if (isset($backtrace[$stackOffset]) && $backtrace[$stackOffset] !== []) {
            $requiredBy             = $backtrace[$stackOffset]['class']
                                    . $backtrace[$stackOffset]['type']
                                    . $backtrace[$stackOffset]['function'];

            $file                   = $backtrace[$stackOffset - 1]['file'] ?? '';
            $line                   = $backtrace[$stackOffset - 1]['line'] ?? '';
        }

        return "The dependency '$key'$withFactory is not found in container '$container', required at $file:$line by $requiredBy, '$forDependency'";
    }

}
