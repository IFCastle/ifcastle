<?php

declare(strict_types=1);

namespace IfCastle\Mcp;

/**
 * One HTTP answer of the MCP endpoint, for the host application to send as is: the status, the headers and the body,
 * which is null for 202, 401 and 405, as the transport asks for no body there.
 */
final readonly class McpResponse
{
    private const int JSON_FLAGS    = JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    /**
     * @param array<string, string> $headers header name => value
     */
    public function __construct(public int $status, public array $headers = [], public ?string $body = null) {}

    /**
     * A JSON-RPC message, answered with no-store: a reply depends on the credential that asked for it.
     *
     * @param array<string, mixed> $message
     */
    public static function json(int $status, array $message): self
    {
        return new self($status, ['Content-Type' => 'application/json', 'Cache-Control' => 'no-store'],
            \json_encode($message, self::JSON_FLAGS));
    }

    /**
     * @param string|int|null $id the request's id; null when the request has none or could not be read
     */
    public static function error(int $status, string|int|null $id, int $code, string $message, mixed $data = null): self
    {
        $error                  = ['code' => $code, 'message' => $message];

        if ($data !== null) {
            $error['data']      = $data;
        }

        return self::json($status, ['jsonrpc' => '2.0', 'id' => $id, 'error' => $error]);
    }
}
