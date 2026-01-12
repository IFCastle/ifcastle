<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader\BootManager;

use PHPUnit\Framework\TestCase;

class BootManagerByDirectoryTest extends TestCase
{
    private string $bootloaderDir;

    #[\Override]
    protected function setUp(): void
    {
        $this->bootloaderDir        = __DIR__ . '/bootloader';

        if (\is_dir($this->bootloaderDir)) {
            // remove all files
            $files = \glob($this->bootloaderDir . '/*');

            foreach ($files as $file) {
                if (\is_file($file)) {
                    \unlink($file);
                }
            }
        }

        if (!\is_dir($this->bootloaderDir)) {
            \mkdir($this->bootloaderDir);
        }
    }

    public function testAddBootloader(): void
    {
        $componentName              = 'componentName';
        $file                       = $this->bootloaderDir . '/' . $componentName . '.ini';

        $bootManager                = new BootManagerByDirectory($this->bootloaderDir);
        $component                  = $bootManager->createComponent($componentName)
                                                  ->add(['bootloader1', 'bootloader2'], ['application1', 'application2'])
                                                  ->add(['bootloader3'], runtimeTags: ['tag1', 'tag2'])
                                                  ->add(['bootloader4'], runtimeTags: ['tag3', 'tag4'], group: 'group1');
        $bootManager->addComponent($component);

        $this->assertFileExists($file);

        $data                       = [
            'isActive'              => '1',
            'description'           => '',
            'group-0'               =>
                [
                    'isActive'       => '1',
                    'bootloader'     =>
                        [
                            0       => 'bootloader1',
                            1       => 'bootloader2',
                        ],
                    'forApplication' =>
                        [
                            0       => 'application1',
                            1       => 'application2',
                        ],
                ],
            'group-1'               =>
                [
                    'isActive'      => '1',
                    'bootloader'    =>
                        [
                            0       => 'bootloader3',
                        ],
                    'runtimeTags' =>
                        [
                            0       => 'tag1',
                            1       => 'tag2',
                        ],
                ],
            'group1'                =>
                [
                    'isActive'      => '1',
                    'bootloader'    =>
                        [
                            0       => 'bootloader4',
                        ],
                    'runtimeTags'   =>
                        [
                            0       => 'tag3',
                            1       => 'tag4',
                        ],
                ],
        ];

        $this->assertEquals($data, \parse_ini_file($file, true, INI_SCANNER_TYPED));
    }

    public function testActivateBootloader(): void
    {
        $componentName              = 'componentName';
        $file                       = $this->bootloaderDir . '/' . $componentName . '.ini';

        $bootManager                = new BootManagerByDirectory($this->bootloaderDir);
        $component                  = $bootManager->createComponent($componentName)
                                                  ->defineDescription('description')
                                                  ->add(['bootloader1'], ['application1'], excludeTags: ['tag1'], isActive: false);

        $component->deactivate();

        $bootManager->addComponent($component);

        $component                  = $bootManager->getComponent($componentName);
        $component->activate();

        $bootManager->updateComponent($component);

        $data                       = [
            'isActive'              => true,
            'description'           => 'description',
            'group-0'               =>
                [
                    'isActive'       => false,
                    'bootloader'     => ['bootloader1'],
                    'forApplication' => ['application1'],
                    'excludeTags'    => ['tag1'],
                ],
        ];

        $this->assertEquals($data, \parse_ini_file($file, true, INI_SCANNER_TYPED));
    }
}
