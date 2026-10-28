<?php

declare(strict_types=1);

namespace IfCastle\Mcp;

/**
 * What the server says about itself: the Implementation object of `initialize` and of the `_meta` of every result of
 * the current revision, the instructions for the model, and the realm of the 401 challenge.
 */
final readonly class ServerInfo
{
    /**
     * @param string $name the program name clients show, as the Implementation `name`
     * @param string $version the program version, as the Implementation `version`
     * @param ?string $title a display name; null leaves it out
     * @param ?string $instructions how a model should use the tools; null leaves it out
     * @param string $realm the realm of `WWW-Authenticate: Bearer`
     */
    public function __construct(
        public string $name,
        public string $version,
        public ?string $title = null,
        public ?string $instructions = null,
        public string $realm = 'mcp',
    ) {}

    /**
     * @return array<string, string>
     */
    public function implementation(): array
    {
        return ['name' => $this->name, 'version' => $this->version] + ($this->title === null ? []
            : ['title' => $this->title]);
    }
}
