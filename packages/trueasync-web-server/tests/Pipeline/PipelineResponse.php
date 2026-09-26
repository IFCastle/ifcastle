<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer\Pipeline;

/**
 * A response as the HTTP client received it.
 */
final readonly class PipelineResponse
{
    /**
     * @param array<string, list<string>> $headers lower-case names
     */
    public function __construct(public int $status, public array $headers, public string $body) {}

    /**
     * @param array{status: string, headers: list<string>, body: string} $answer
     */
    public static function fromClient(array $answer): self
    {
        $headers                    = [];

        foreach ($answer['headers'] as $line) {
            [$name, $value]         = \array_pad(\explode(':', $line, 2), 2, '');
            $headers[\strtolower(\trim($name))][] = \trim($value);
        }

        return new self((int) (\explode(' ', $answer['status'], 3)[1] ?? 0), $headers, $answer['body']);
    }

    /**
     * @return list<string>
     */
    public function header(string $name): array
    {
        return $this->headers[\strtolower($name)] ?? [];
    }

    /**
     * @return array<mixed>
     */
    public function json(): array
    {
        return \json_decode($this->body, true, 512, JSON_THROW_ON_ERROR);
    }
}
