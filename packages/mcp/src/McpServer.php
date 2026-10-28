<?php

declare(strict_types=1);

namespace IfCastle\Mcp;

use IfCastle\Protocol\Exceptions\TooManyRequests;
use IfCastle\Protocol\Http\HttpRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * A stateless MCP endpoint over Streamable HTTP that serves tools in two protocol eras at once, every answer
 * `application/json`, never an event stream or a session.
 *
 * - 2026-07-28, the current revision: no handshake; every request names its version in
 *   `params._meta["io.modelcontextprotocol/protocolVersion"]` and mirrors it, its method and a tool's name into the
 *   `MCP-Protocol-Version`, `Mcp-Method` and `Mcp-Name` headers; `server/discover` describes the server; every result
 *   carries `resultType` and the server's Implementation in `_meta`.
 * - 2025-11-25 (and 2025-06-18) for clients that open with `initialize`: the handshake is answered without a session
 *   id, and later requests carry the version in `MCP-Protocol-Version` alone.
 *
 * The era is chosen per request by its body, as the 2026-07-28 versioning page describes: `initialize` selects the
 * older era, a version in `_meta` the current one, and anything else is an older-era request that must carry the
 * header. A request without that header is refused rather than taken as 2025-03-26, so no client older than
 * 2025-06-18 is served.
 *
 * Every POST is authenticated before its body is read; the Origin check runs first, against DNS rebinding.
 */
final class McpServer
{
    public const string CURRENT = '2026-07-28';
    /** The older-era versions answered, newest first; `initialize` falls back to the first. */
    public const array LEGACY = ['2025-11-25', '2025-06-18'];
    /** One JSON-RPC message; a larger body answers 413. */
    public const int MAX_BODY = 65536;

    private const string META_VERSION = 'io.modelcontextprotocol/protocolVersion';
    private const string META_CAPABILITIES = 'io.modelcontextprotocol/clientCapabilities';
    private const string META_SERVER = 'io.modelcontextprotocol/serverInfo';
    /** JSON-RPC error codes of JSON-RPC 2.0 and of the MCP specification. */
    private const int PARSE_ERROR = -32700;
    private const int INVALID_REQUEST = -32600;
    private const int METHOD_NOT_FOUND = -32601;
    private const int INVALID_PARAMS = -32602;
    private const int INTERNAL_ERROR = -32603;
    private const int HEADER_MISMATCH = -32020;
    private const int UNSUPPORTED_VERSION = -32022;
    /** `Mcp-Name` of a name outside plain ASCII: `=?base64?<value>?=`. */
    private const string ENCODED_HEADER = '/^=\?base64\?([A-Za-z0-9+\/]*={0,2})\?=$/D';
    /** The tool list varies with the principal's rights, which change at any time: never cache it. */
    private const int LIST_TTL_MS = 0;

    /**
     * @param list<string> $allowedOrigins exact `Origin` values a request may carry; a request without the header
     *     (every MCP client but a browser) is not checked
     */
    public function __construct(
        private readonly ServerInfo $info,
        private readonly ToolRegistry $tools,
        private readonly BearerAuthenticatorInterface $authenticator,
        private readonly array $allowedOrigins = [],
        private readonly ?LoggerInterface $logger = null,
    ) {}

    /**
     * Answers one HTTP request to the endpoint. Never throws for anything the client sent: a tool's unexpected
     * exception is logged and answered 500 with JSON-RPC -32603, and a TooManyRequests of the authenticator 429.
     */
    public function handle(HttpRequestInterface $request): McpResponse
    {
        if ($request->getMethod() !== 'POST') {
            return new McpResponse(405, ['Allow' => 'POST']);
        }

        $origin = $request->getHeaderLine('Origin');
        if ($origin !== '' && !\in_array($origin, $this->allowedOrigins, true)) {
            return McpResponse::error(403, null, self::INVALID_REQUEST, 'Origin is not allowed');
        }

        $body = $request->getBody();
        if (\strlen($body) > self::MAX_BODY) {
            return McpResponse::error(413, null, self::INVALID_REQUEST, 'A request body is at most '
                . self::MAX_BODY . ' bytes');
        }

        try {
            $principal = $this->authenticator->authenticate(self::bearer($request), $request);
        } catch (TooManyRequests) {
            return new McpResponse(429, ['Retry-After' => '60']);
        }

        if ($principal === null) {
            return new McpResponse(401, ['WWW-Authenticate' => 'Bearer realm="' . $this->info->realm . '"']);
        }

        try {
            $message = \json_decode($body, false, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return McpResponse::error(400, null, self::PARSE_ERROR, 'Parse error');
        }

        return $this->message($request, $message, $principal);
    }

    private function message(HttpRequestInterface $request, mixed $message, object $principal): McpResponse
    {
        if (\is_array($message)) {
            return McpResponse::error(400, null, self::INVALID_REQUEST, 'A batch is not served: send one message');
        }

        if (!$message instanceof \stdClass || ($message->jsonrpc ?? null) !== '2.0') {
            return McpResponse::error(400, null, self::INVALID_REQUEST, 'Not a JSON-RPC 2.0 message');
        }

        $hasId = \property_exists($message, 'id');
        $id = $message->id ?? null;
        if ($hasId && !\is_string($id) && !\is_int($id)) {
            return McpResponse::error(400, null, self::INVALID_REQUEST, 'id must be a string or an integer');
        }

        if (!\property_exists($message, 'method')) {
            // A JSON-RPC response from the client: this server sends no requests, so nothing awaits it.
            return \property_exists($message, 'result') || \property_exists($message, 'error')
                ? new McpResponse(202)
                : McpResponse::error(400, $id, self::INVALID_REQUEST, 'method is required');
        }

        $params = $message->params ?? new \stdClass();
        if (!\is_string($message->method) || !$params instanceof \stdClass) {
            return McpResponse::error(400, $id, self::INVALID_REQUEST, 'method must be a string and params an object');
        }

        $call = new Call($message->method, $params, $hasId ? $id : null, !$hasId);
        $meta = $params->_meta ?? null;
        if ($call->method !== 'initialize' && $meta instanceof \stdClass
            && \property_exists($meta, self::META_VERSION)) {
            return $this->current($request, $call, $meta, $principal);
        }

        return $this->legacy($request, $call, $principal);
    }

    /** A request of the 2026-07-28 era. */
    private function current(HttpRequestInterface $request, Call $call, \stdClass $meta, object $principal): McpResponse
    {
        $version = $meta->{self::META_VERSION};
        if ($version !== self::CURRENT) {
            return McpResponse::error(400, $call->id, self::UNSUPPORTED_VERSION, 'Unsupported protocol version',
                ['supported' => [self::CURRENT, ...self::LEGACY], 'requested' => $version]);
        }

        if (!($meta->{self::META_CAPABILITIES} ?? null) instanceof \stdClass) {
            return McpResponse::error(400, $call->id, self::INVALID_PARAMS, '_meta must carry '
                . self::META_CAPABILITIES);
        }

        $mismatch = $this->headerMismatch($request, $call, $version);
        if ($mismatch !== null) {
            return McpResponse::error(400, $call->id, self::HEADER_MISMATCH, 'Header mismatch: ' . $mismatch);
        }

        if ($call->notification) {
            return new McpResponse(202);
        }

        $result = match ($call->method) {
            'server/discover' => ['supportedVersions' => [self::CURRENT, ...self::LEGACY],
                'capabilities' => self::capabilities()] + $this->instructions()
                + ['ttlMs' => self::LIST_TTL_MS, 'cacheScope' => 'private'],
            'tools/list' => $this->toolList($principal) + ['ttlMs' => self::LIST_TTL_MS, 'cacheScope' => 'private'],
            'tools/call' => $this->toolCall($call, $principal),
            default => null,
        };

        if ($result instanceof McpResponse) {
            return $result;
        }

        if ($result === null) {
            return McpResponse::error(404, $call->id, self::METHOD_NOT_FOUND, 'Method not found: ' . $call->method);
        }

        return self::result($call, ['resultType' => 'complete'] + $result
            + ['_meta' => [self::META_SERVER => $this->info->implementation()]]);
    }

    /** A request of the 2025-11-25 era (or 2025-06-18), `initialize` included. */
    private function legacy(HttpRequestInterface $request, Call $call, object $principal): McpResponse
    {
        if ($call->method !== 'initialize') {
            $header = $request->getHeaderLine('MCP-Protocol-Version');
            if ($header === '') {
                return McpResponse::error(400, $call->id, self::INVALID_REQUEST,
                    'MCP-Protocol-Version is required, or io.modelcontextprotocol/protocolVersion in params._meta');
            }

            if ($header === self::CURRENT) {
                // A request of the current era must name its version in _meta too (basic/index, request metadata).
                return McpResponse::error(400, $call->id, self::INVALID_PARAMS, 'params._meta must carry '
                    . self::META_VERSION);
            }

            if (!\in_array($header, self::LEGACY, true)) {
                return McpResponse::error(400, $call->id, self::UNSUPPORTED_VERSION, 'Unsupported protocol version',
                    ['supported' => [self::CURRENT, ...self::LEGACY], 'requested' => $header]);
            }
        }

        if ($call->notification) {
            return new McpResponse(202);
        }

        $result = match ($call->method) {
            'initialize' => $this->initialize($call),
            'ping' => new \stdClass(),
            'tools/list' => $this->toolList($principal),
            'tools/call' => $this->toolCall($call, $principal),
            default => null,
        };

        if ($result instanceof McpResponse) {
            return $result;
        }

        return $result === null
            ? McpResponse::error(404, $call->id, self::METHOD_NOT_FOUND, 'Method not found: ' . $call->method)
            : self::result($call, $result);
    }

    /**
     * @return array<string, mixed>
     */
    private function initialize(Call $call): array
    {
        $requested = $call->params->protocolVersion ?? null;

        return ['protocolVersion' => \in_array($requested, self::LEGACY, true) ? $requested : self::LEGACY[0],
            'capabilities' => self::capabilities(), 'serverInfo' => $this->info->implementation()]
            + $this->instructions();
    }

    /**
     * @return array{tools: list<array<string, mixed>>}
     */
    private function toolList(object $principal): array
    {
        return ['tools' => \array_map(static fn(Tool $tool): array => $tool->definition(),
            $this->tools->listedFor($principal))];
    }

    /**
     * @return array<string, mixed>|McpResponse the CallToolResult, or the JSON-RPC error that refuses the call
     */
    private function toolCall(Call $call, object $principal): array|McpResponse
    {
        $name = $call->params->name ?? null;
        $tool = \is_string($name) ? $this->tools->find($name) : null;
        if ($tool === null) {
            return McpResponse::error(200, $call->id, self::INVALID_PARAMS, 'Unknown tool: '
                . (\is_string($name) ? $name : '(no name)'));
        }

        $arguments = $call->params->arguments ?? new \stdClass();
        if (!$arguments instanceof \stdClass) {
            return McpResponse::error(200, $call->id, self::INVALID_PARAMS, 'arguments must be an object');
        }

        $invalid = SchemaValidator::check($tool->inputSchema, $arguments);
        if ($invalid !== null) {
            return self::toolFailure($invalid);
        }

        try {
            $structured = ($tool->handler)(self::associative($arguments), $principal);
        } catch (ToolError $error) {
            return self::toolFailure($error->getMessage());
        } catch (\Throwable $error) {
            $this->logger?->error('MCP tool {tool} failed: {message}', ['tool' => $tool->name,
                'message' => $error->getMessage(), 'exception' => $error]);

            return McpResponse::error(500, $call->id, self::INTERNAL_ERROR, 'Internal error');
        }

        $object = $structured === [] ? new \stdClass() : $structured;

        return ['content' => [['type' => 'text', 'text' => \json_encode($object,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]],
            'structuredContent' => $object, 'isError' => false];
    }

    /** The first validation failure among the headers the current era mirrors from the body; null when all match. */
    private function headerMismatch(HttpRequestInterface $request, Call $call, string $version): ?string
    {
        $expected = ['MCP-Protocol-Version' => $version, 'Mcp-Method' => $call->method];
        if ($call->method === 'tools/call') {
            $expected['Mcp-Name'] = $call->params->name ?? null;
        }

        foreach ($expected as $header => $value) {
            if (!$request->hasHeader($header)) {
                return "$header is required";
            }

            $sent = $request->getHeaderLine($header);
            if ($header === 'Mcp-Name' && \preg_match(self::ENCODED_HEADER, $sent, $encoded) === 1) {
                $sent = \base64_decode($encoded[1], true);
            }

            if ($sent !== $value) {
                return "$header does not match the request body";
            }
        }

        return null;
    }

    /**
     * @return array{instructions?: string}
     */
    private function instructions(): array
    {
        return $this->info->instructions === null ? [] : ['instructions' => $this->info->instructions];
    }

    /**
     * @return array{tools: array{listChanged: false}}
     */
    private static function capabilities(): array
    {
        return ['tools' => ['listChanged' => false]];
    }

    /**
     * @param array<string, mixed>|\stdClass $result
     */
    private static function result(Call $call, array|\stdClass $result): McpResponse
    {
        return McpResponse::json(200, ['jsonrpc' => '2.0', 'id' => $call->id, 'result' => $result]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function toolFailure(string $message): array
    {
        return ['content' => [['type' => 'text', 'text' => $message]], 'isError' => true];
    }

    /** The token of `Authorization: Bearer <token>`; the scheme is case-insensitive (RFC 9110 §11.1). */
    private static function bearer(HttpRequestInterface $request): ?string
    {
        return \preg_match('/^Bearer +(\S+)$/iD', $request->getHeaderLine('Authorization'), $match) === 1
            ? $match[1]
            : null;
    }

    /**
     * Arguments as the handler takes them: objects as associative arrays, arrays as lists.
     *
     * @return array<string, mixed>
     */
    private static function associative(\stdClass $arguments): array
    {
        return \json_decode(\json_encode($arguments, JSON_THROW_ON_ERROR), true, 64, JSON_THROW_ON_ERROR);
    }
}
