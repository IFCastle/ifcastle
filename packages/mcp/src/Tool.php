<?php

declare(strict_types=1);

namespace IfCastle\Mcp;

/**
 * One tool: its definition as `tools/list` sends it and the function `tools/call` runs.
 *
 * The handler receives the arguments, already checked against the input schema and decoded into arrays, and the
 * principal of BearerAuthenticatorInterface. It returns a JSON object as an associative array, sent as
 * `structuredContent` and, serialised, as the text content; it throws ToolError for a refusal the model should read.
 */
final readonly class Tool
{
    /** The names the specification recommends: they need no encoding in the Mcp-Name header. */
    private const string NAME = '/^[A-Za-z0-9_.-]{1,128}$/D';

    /**
     * @param array<string, mixed> $inputSchema a JSON Schema of the subset SchemaValidator checks, its root of type
     *     object
     * @param \Closure(array<string, mixed>, object): array<string, mixed> $handler
     * @param ?\Closure(object): bool $listed whether `tools/list` shows the tool to a principal; null shows it to
     *     every principal. A call is never refused by it: the handler checks its own rights, since they can change
     *     between the list and the call.
     * @throws \InvalidArgumentException when the name is outside the recommended characters or the schema is
     *     outside the subset
     */
    public function __construct(
        public string $name,
        public string $description,
        public array $inputSchema,
        public \Closure $handler,
        public ToolAnnotations $annotations = new ToolAnnotations(),
        public ?string $title = null,
        public ?\Closure $listed = null,
    ) {
        if (\preg_match(self::NAME, $name) !== 1) {
            throw new \InvalidArgumentException('A tool name is 1-128 characters of A-Z, a-z, 0-9, _, - and .');
        }

        if (($inputSchema['type'] ?? null) !== 'object') {
            throw new \InvalidArgumentException("The input schema of $name must be of type object");
        }

        SchemaValidator::assertSupported($inputSchema, $name);
    }

    public function isListedFor(object $principal): bool
    {
        return $this->listed === null || ($this->listed)($principal);
    }

    /**
     * @return array<string, mixed> the Tool object of the specification
     */
    public function definition(): array
    {
        return ['name' => $this->name] + ($this->title === null ? [] : ['title' => $this->title])
            + ['description' => $this->description, 'inputSchema' => $this->inputSchema,
                'annotations' => $this->annotations->toArray()];
    }
}
