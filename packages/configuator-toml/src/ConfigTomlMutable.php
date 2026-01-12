<?php

declare(strict_types=1);

namespace IfCastle\Configurator\Toml;

use Devium\Toml\Toml;
use Devium\Toml\TomlError;
use IfCastle\DI\ConfigMutableInterface;
use IfCastle\DI\Exceptions\ConfigException;
use IfCastle\Exceptions\RuntimeException;
use IfCastle\OsUtilities\FileSystem\Exceptions\FileIsNotExistException;
use IfCastle\OsUtilities\Safe;

class ConfigTomlMutable extends ConfigToml implements ConfigMutableInterface
{
    protected bool $wasModified = false;

    public function __construct(string $file, protected bool $isReadOnly = false)
    {
        parent::__construct($file);
    }

    /**
     * @throws RuntimeException
     * @throws TomlError
     * @throws ConfigException
     * @throws \ErrorException
     */
    public function save(): void
    {
        $this->throwReadOnly();

        $content                    = Toml::encode($this->data);
        $content                    = $this->afterBuild($content);

        $result                     = Safe::execute(fn() => \file_put_contents($this->file, $content));

        if (false === $result) {
            throw new RuntimeException('Error occurred while saving ini file: ' . $this->file);
        }

        $this->wasModified          = false;
    }

    protected function afterBuild(string $content): string
    {
        return $content;
    }

    /**
     * @throws RuntimeException
     * @throws FileIsNotExistException
     * @throws \ErrorException
     * @throws ConfigException
     */
    #[\Override]
    public function set(string $node, mixed $value): static
    {
        $this->throwReadOnly();
        $this->load();
        $this->wasModified          = true;
        $current                    = &$this->referenceBy($node);
        $current                    = $value;

        return $this;
    }

    /**
     * @throws RuntimeException
     * @throws FileIsNotExistException
     * @throws ConfigException
     * @throws \ErrorException
     */
    #[\Override]
    public function setSection(string $node, array $value): static
    {
        return $this->set($node, $value);
    }

    /**
     * @throws RuntimeException
     * @throws FileIsNotExistException
     * @throws ConfigException
     * @throws \ErrorException
     */
    #[\Override]
    public function merge(array $config): static
    {
        $this->throwReadOnly();
        $this->load();

        $this->wasModified         = true;

        $this->data                 = \array_merge($this->data, $config);

        return $this;
    }

    /**
     * @throws RuntimeException
     * @throws FileIsNotExistException
     * @throws \ErrorException
     * @throws ConfigException
     */
    #[\Override]
    public function mergeSection(string $node, array $config): static
    {
        $this->throwReadOnly($node);
        $this->load();
        $this->wasModified          = true;
        $current                    = &$this->referenceBy($node);

        $current                    = \array_merge($current, $config);

        return $this;
    }

    /**
     * @throws RuntimeException
     * @throws FileIsNotExistException
     * @throws ConfigException
     * @throws \ErrorException
     */
    #[\Override]
    public function remove(string ...$path): static
    {
        $this->throwReadOnly();
        $this->load();

        $current                    = &$this->data;

        while ($path !== []) {
            $key                    = \array_shift($path);

            if (!\is_array($current) || !\array_key_exists($key, $current)) {
                return $this;
            }

            $current                = &$current[$key];
        }

        $this->wasModified          = true;

        unset($current);

        return $this;
    }

    /**
     * @throws RuntimeException
     * @throws FileIsNotExistException
     * @throws \ErrorException
     * @throws ConfigException
     */
    #[\Override]
    public function reset(): static
    {
        $this->throwReadOnly();
        $this->resetLoadedData();

        $this->wasModified          = true;

        $this->data                 = [];

        return $this;
    }

    #[\Override]
    public function asImmutable(): static
    {
        $this->isReadOnly           = true;
        return $this;
    }

    #[\Override]
    public function cloneAsMutable(): static
    {
        return new static($this->file, false);
    }

    #[\Override]
    protected function load(): void
    {
        try {
            parent::load();
        } catch (FileIsNotExistException) {
            Safe::execute(fn() => \file_put_contents($this->file, ''));
            parent::load();
        }
    }

    protected function &referenceBy(string $node): mixed
    {
        $path                       = \explode('.', $node);

        $current                    = &$this->data;

        do {
            $key                    = \array_shift($path);

            if (!\array_key_exists($key, $current) || !\is_array($current[$key])) {
                $current[$key]      = [];
            }

            $current                = &$current[$key];
        } while ($path !== []);

        return $current;
    }

    /**
     * @throws ConfigException
     */
    protected function throwReadOnly(string $node = ''): void
    {
        if ($this->isReadOnly) {
            throw new ConfigException('The config key ' . $node . ' is read only');
        }
    }
}
