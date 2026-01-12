<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader\BootManager;

final class BootManagerApplication
{
    public const string CONFIGURATOR = 'configurator';

    /**
     * @param array<string, string>|null $command
     */
    public static function run(string $appDir, ?array $command = null): never
    {
        $appDir .= '/bootloader';

        if (\file_exists($appDir) === false) {
            echo 'Bootloader directory not found: ' . $appDir . PHP_EOL;
            exit(1);
        }

        if (\file_exists($appDir . '/bootloader.php')) {
            $manager                = include_once $appDir . '/bootloader.php';
        } else {
            $manager                = new BootManagerByDirectory($appDir);
        }

        if ($manager instanceof BootManagerInterface === false) {
            echo 'Invalid bootloader manager: ' . \get_debug_type($manager) . PHP_EOL;
            exit(2);
        }

        if ($command === null) {
            echo 'No command specified. Exiting...' . PHP_EOL;
            exit(0);
        }

        if (empty($command['action'])) {
            echo 'No command action specified. Exiting...' . PHP_EOL;
            exit(1);
        }

        switch ($command['action']) {
            case 'add':
                self::add($manager, $command);
                break;
            case 'activate':
                self::activate($manager, $command);
                break;
            case 'disable':
                self::disable($manager, $command);
                break;
            case 'remove':
                self::remove($manager, $command);
                break;
            default:
                echo 'Invalid command action specified. Exiting...' . PHP_EOL;
                exit(2);
        }

        exit();
    }

    /**
     * @param array<string, scalar|array<scalar>> $command
     *
     */
    public static function add(BootManagerInterface $bootManager, array $command): void
    {
        foreach (['component', 'bootloaders'] as $key) {
            if (empty($command[$key])) {
                echo 'Missing required parameter: ' . $key . PHP_EOL;
                exit(3);
            }
        }

        $component                  = $bootManager->createComponent($command['component']);
        $component->add(
            $command['bootloaders'],
            $command['applications'] ?? [],
            $command['tags'] ?? [],
            $command['excludeTags'] ?? []
        );

        $bootManager->addComponent($component);
    }

    /**
     * @param array<string, string> $command
     *
     */
    public static function activate(BootManagerInterface $bootManager, array $command): void
    {
        if (empty($command['component'])) {
            echo 'Missing required parameter: component' . PHP_EOL;
            exit(4);
        }

        $component                  = $bootManager->getComponent($command['component']);
        $component->activate();

        $bootManager->updateComponent($component);
    }

    /**
     * @param array<string, string> $command
     *
     */
    public static function disable(BootManagerInterface $bootManager, array $command): void
    {
        if (empty($command['component'])) {
            echo 'Missing required parameter: component' . PHP_EOL;
            exit(5);
        }

        $component                  = $bootManager->getComponent($command['component']);
        $component->deactivate();

        $bootManager->updateComponent($component);
    }

    /**
     * @param array<string, string> $command
     *
     */
    public static function remove(BootManagerInterface $bootManager, array $command): void
    {
        if (empty($command['component'])) {
            echo 'Missing required parameter: component' . PHP_EOL;
            exit(6);
        }

        $bootManager->removeComponent($command['component']);
    }
}
