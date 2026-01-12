<?php

declare(strict_types=1);

namespace IfCastle\Configurator\Toml;

use Devium\Toml\Toml;
use Devium\Toml\TomlError;
use IfCastle\DI\ConfigInterface;
use IfCastle\Exceptions\RuntimeException;
use IfCastle\OsUtilities\FileSystem\Exceptions\FileIsNotExistException;
use IfCastle\OsUtilities\Safe;

class ConfigToml implements ConfigInterface
{
    private bool $isLoaded          = false;

    /**
     * @var array<string, mixed>
     */
    protected array $data           = [];

    public function __construct(protected string $file) {}

    /**
     * @throws RuntimeException
     * @throws FileIsNotExistException
     * @throws \ErrorException
     */
    protected function load(): void
    {
        if ($this->isLoaded) {
            return;
        }

        $this->isLoaded             = true;

        if (false === \file_exists($this->file)) {
            throw new FileIsNotExistException($this->file);
        }

        $data                       = Safe::execute(fn() => \file_get_contents($this->file));

        try {
            $data                   = Toml::decode($data, asArray: true, asFloat: true);
        } catch (TomlError $exception) {
            throw new RuntimeException([
                'template'          => 'Error occurred while parse TOML file: {file}',
                'message'           => $exception->getMessage(),
                'file'              => $this->file,
            ]);
        }

        // Convert TomlDateTime objects to DateTimeImmutable objects
        \array_walk_recursive($data, static function (&$value) {
            if (\is_object($value)) {
                $value = (string) $value;
            }
        });

        $this->data                 = $data;
    }

    public function find(string...$path): mixed
    {
        $this->load();

        $current                    = $this->data;

        while ($path !== []) {
            $key                    = \array_shift($path);

            if (!\is_array($current) || !\array_key_exists($key, $current)) {
                return null;
            }

            $current                = $current[$key];
        }

        return $current;
    }

    /**
     * @throws RuntimeException
     * @throws FileIsNotExistException
     * @throws \ErrorException
     */
    #[\Override]
    public function findValue(string $key, mixed $default = null): mixed
    {
        $this->load();

        $path                       = \explode('.', $key);

        if (\count($path) === 1) {
            return $this->data[$key] ?? $default;
        }

        return $this->find(...$path) ?? $default;
    }

    /**
     * @throws RuntimeException
     * @throws FileIsNotExistException
     * @throws \ErrorException
     */
    #[\Override]
    public function findSection(string $section): array
    {
        $this->load();

        $result                     = $this->findValue($section);

        if (!\is_array($result)) {
            return [];
        }

        return $result;
    }

    /**
     * @throws RuntimeException
     * @throws FileIsNotExistException
     * @throws \ErrorException
     */
    #[\Override]
    public function requireValue(string $key): mixed
    {
        $this->load();

        $value                      = $this->findValue($key);

        if ($value === null) {
            throw new RuntimeException('Value not found for key: ' . $key);
        }

        return $value;
    }

    /**
     * @throws RuntimeException
     * @throws FileIsNotExistException
     * @throws \ErrorException
     */
    #[\Override]
    public function requireSection(string $section): array
    {
        $this->load();

        $result                     = $this->findSection($section);

        if ($result === []) {
            throw new RuntimeException('Section not found or empty: ' . $section);
        }

        return $result;
    }

    protected function resetLoadedData(): void
    {
        $this->isLoaded             = false;
    }
}
