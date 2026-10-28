<?php

declare(strict_types=1);

namespace IfCastle\Mcp;

use IfCastle\Protocol\Exceptions\TooManyRequests;
use IfCastle\Protocol\Http\HttpRequestInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

/**
 * The transport and both protocol eras, against the rules of the MCP specification: 2026-07-28 basic/versioning,
 * basic/index (request _meta), basic/transports/streamable-http (headers, status codes), server/discover and
 * server/tools; 2025-11-25 basic/lifecycle and basic/transports.
 */
final class McpServerTest extends TestCase
{
    private const string TOKEN = 'good-token';
    private const string ORIGIN = 'https://library.example';

    /** @var list<?string> the bearer values the authenticator saw */
    private array $seen = [];
    private bool $throttled = false;
    /** @var list<string> */
    private array $logged = [];
    /** @var list<array{array<string, mixed>, object}> */
    private array $calls = [];

    private function server(): McpServer
    {
        $authenticator = new class ($this) implements BearerAuthenticatorInterface {
            public function __construct(private readonly McpServerTest $test) {}

            public function authenticate(?string $token, HttpRequestInterface $request): ?object
            {
                return $this->test->authenticate($token);
            }
        };
        $logger = new class (function (string $line): void {
            $this->logged[] = $line;
        }) extends AbstractLogger {
            public function __construct(private readonly \Closure $record) {}

            public function log($level, \Stringable|string $message, array $context = []): void
            {
                ($this->record)($level . ' ' . $context['tool'] . ' ' . $context['message']);
            }
        };
        $tools = new ToolRegistry()
            ->add(new Tool('echo', 'Returns its arguments', ['type' => 'object', 'properties' => [
                'text' => ['type' => 'string', 'maxLength' => 3], 'count' => ['type' => 'integer', 'minimum' => 1],
                'tags' => ['type' => 'array', 'items' => ['type' => 'string']]],
                'required' => ['text'], 'additionalProperties' => false],
                function (array $arguments, object $principal): array {
                    $this->calls[] = [$arguments, $principal];

                    return ['echo' => $arguments];
                }, ToolAnnotations::read(), 'Echo'))
            ->add(new Tool('refuse', 'Always refuses', ['type' => 'object'],
                static fn(array $arguments, object $principal): array => throw new ToolError('Work not found')))
            ->add(new Tool('break', 'Always fails', ['type' => 'object'],
                static fn(array $arguments, object $principal): array => throw new \RuntimeException('secret detail')))
            ->add(new Tool('hidden', 'Not listed for anybody', ['type' => 'object'],
                static fn(array $arguments, object $principal): array => [],
                listed: static fn(object $principal): bool => false));

        return new McpServer(new ServerInfo('test-server', '1.2.3', 'Test server', 'Use the tools', 'lib'), $tools,
            $authenticator, [self::ORIGIN], $logger);
    }

    /** The authenticator of server(): self::TOKEN is a principal, anything else none. */
    public function authenticate(?string $token): ?object
    {
        $this->seen[] = $token;
        if ($this->throttled) {
            throw new TooManyRequests();
        }

        return $token === self::TOKEN ? (object) ['user' => 7] : null;
    }

    /**
     * @param array<string, string> $headers added to a valid Authorization header
     */
    private function post(string $body, array $headers = []): McpResponse
    {
        return $this->server()->handle(new FakeRequest('POST', $headers + ['Authorization' => 'Bearer ' . self::TOKEN],
            $body));
    }

    /**
     * A request of the current era with its mirrored headers.
     *
     * @param array<string, mixed> $params
     * @param array<string, string> $headers replacing the mirrored ones
     */
    private function current(string $method, array $params = [], array $headers = [], string $version = '2026-07-28'): McpResponse
    {
        $params['_meta'] = ['io.modelcontextprotocol/protocolVersion' => $version,
            'io.modelcontextprotocol/clientCapabilities' => new \stdClass()];
        $mirrored = ['MCP-Protocol-Version' => $version, 'Mcp-Method' => $method]
            + (isset($params['name']) ? ['Mcp-Name' => $params['name']] : []);

        return $this->post(self::message($method, $params), $headers + $mirrored);
    }

    /**
     * @param array<string, mixed> $params
     */
    private function legacy(string $method, array $params = [], ?string $version = '2025-11-25', bool $notification = false): McpResponse
    {
        return $this->post(self::message($method, $params, $notification ? null : 1),
            $version === null ? [] : ['MCP-Protocol-Version' => $version]);
    }

    /**
     * @param array<string, mixed> $params
     */
    private static function message(string $method, array $params = [], ?int $id = 1): string
    {
        return \json_encode(['jsonrpc' => '2.0'] + ($id === null ? [] : ['id' => $id]) + ['method' => $method]
            + ($params === [] ? [] : ['params' => $params]), JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    private static function decoded(McpResponse $response, int $status = 200): array
    {
        self::assertSame($status, $response->status, (string) $response->body);
        self::assertSame('application/json', $response->headers['Content-Type']);

        return \json_decode((string) $response->body, true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @return array{int, int, mixed} HTTP status, JSON-RPC error code, error data
     */
    private static function error(McpResponse $response): array
    {
        $error = \json_decode((string) $response->body, true, flags: JSON_THROW_ON_ERROR)['error'];

        return [$response->status, $error['code'], $error['data'] ?? null];
    }

    public function testOnlyPostIsServed(): void
    {
        foreach (['GET', 'DELETE', 'PUT'] as $method) {
            $response = $this->server()->handle(new FakeRequest($method, ['Authorization' => 'Bearer ' . self::TOKEN]));
            self::assertSame([405, ['Allow' => 'POST'], null], [$response->status, $response->headers,
                $response->body]);
        }

        self::assertSame([], $this->seen, 'refused before the credential is checked');
    }

    public function testAForeignOriginIsForbiddenAndAnAllowedOneServed(): void
    {
        $response = $this->post(self::message('ping'), ['Origin' => 'https://evil.example',
            'MCP-Protocol-Version' => '2025-11-25']);
        self::assertSame([403, -32600, null], self::error($response));
        self::assertNull(\json_decode((string) $response->body, true)['id']);
        self::assertSame(200, $this->post(self::message('ping'), ['Origin' => self::ORIGIN,
            'MCP-Protocol-Version' => '2025-11-25'])->status);
        self::assertSame(200, $this->legacy('ping')->status, 'no Origin header: not a browser');
    }

    public function testAMissingOrUnknownCredentialIsChallengedWithoutABody(): void
    {
        $server = $this->server();
        foreach ([[], ['Authorization' => 'Basic Zm9vOmJhcg=='], ['Authorization' => 'Bearer'],
            ['Authorization' => 'Bearer other-token']] as $headers) {
            $response = $server->handle(new FakeRequest('POST', $headers, self::message('ping')));
            self::assertSame([401, ['WWW-Authenticate' => 'Bearer realm="lib"'], null],
                [$response->status, $response->headers, $response->body]);
        }

        self::assertSame([null, null, null, 'other-token'], $this->seen, 'every failure reaches the authenticator');
        self::assertSame(200, $server->handle(new FakeRequest('POST', ['Authorization' => 'bearer ' . self::TOKEN,
            'MCP-Protocol-Version' => '2025-11-25'], self::message('ping')))->status, 'the scheme ignores case');
    }

    public function testAThrottledCredentialAnswers429(): void
    {
        $this->throttled = true;
        $response = $this->legacy('ping');
        self::assertSame([429, null], [$response->status, $response->body]);
    }

    public function testMalformedBodiesAreRefused(): void
    {
        self::assertSame([400, -32700, null], self::error($this->post('{"jsonrpc":')));
        self::assertSame([400, -32600, null], self::error($this->post('[' . self::message('ping') . ']')),
            'a batch');
        self::assertSame([400, -32600, null], self::error($this->post('{"jsonrpc":"1.0","id":1,"method":"ping"}')));
        self::assertSame([400, -32600, null], self::error($this->post('{"jsonrpc":"2.0","id":null,"method":"ping"}')));
        self::assertSame([400, -32600, null], self::error($this->post('{"jsonrpc":"2.0","id":1,"method":"ping",'
            . '"params":[]}')));
        self::assertSame(413, $this->post(\str_repeat(' ', McpServer::MAX_BODY + 1))->status);
        self::assertSame([], $this->calls);
    }

    public function testAClientResponseIsAcceptedWithoutABody(): void
    {
        $response = $this->post('{"jsonrpc":"2.0","id":5,"result":{}}');
        self::assertSame([202, null], [$response->status, $response->body]);
    }

    public function testInitializeOpensTheOlderEraWithoutASession(): void
    {
        $response = $this->legacy('initialize', ['protocolVersion' => '2025-11-25', 'capabilities' => new \stdClass(),
            'clientInfo' => ['name' => 'client', 'version' => '1']], version: null);
        self::assertSame(['jsonrpc' => '2.0', 'id' => 1, 'result' => ['protocolVersion' => '2025-11-25',
            'capabilities' => ['tools' => ['listChanged' => false]],
            'serverInfo' => ['name' => 'test-server', 'version' => '1.2.3', 'title' => 'Test server'],
            'instructions' => 'Use the tools']], self::decoded($response));
        self::assertArrayNotHasKey('Mcp-Session-Id', $response->headers);
        self::assertSame('2025-06-18', self::decoded($this->legacy('initialize', ['protocolVersion' => '2025-06-18'],
            version: null))['result']['protocolVersion']);
        self::assertSame('2025-11-25', self::decoded($this->legacy('initialize', ['protocolVersion' => '2024-11-05'],
            version: null))['result']['protocolVersion'], 'an unserved version is answered with the latest');

        $initialized = $this->legacy('notifications/initialized', notification: true);
        self::assertSame([202, null], [$initialized->status, $initialized->body]);
    }

    public function testAnOlderEraRequestNeedsAServedVersionHeader(): void
    {
        self::assertSame([400, -32600, null], self::error($this->legacy('tools/list', version: null)));
        self::assertSame([400, -32602, null], self::error($this->legacy('tools/list', version: '2026-07-28')),
            'the current era without its _meta');
        self::assertSame([400, -32022, ['supported' => ['2026-07-28', '2025-11-25', '2025-06-18'],
            'requested' => '2025-03-26']], self::error($this->legacy('tools/list', version: '2025-03-26')));
        self::assertSame('{"jsonrpc":"2.0","id":1,"result":{}}', $this->legacy('ping')->body);
        self::assertSame([404, -32601, null], self::error($this->legacy('resources/list')));
    }

    public function testToolsListShowsTheListedToolsWithTheirDefinitions(): void
    {
        $older = self::decoded($this->legacy('tools/list'))['result'];
        self::assertSame(['echo', 'refuse', 'break'], \array_column($older['tools'], 'name'));
        self::assertSame(['name' => 'echo', 'title' => 'Echo', 'description' => 'Returns its arguments'],
            \array_slice($older['tools'][0], 0, 3));
        self::assertSame(['readOnlyHint' => true, 'destructiveHint' => false, 'idempotentHint' => true,
            'openWorldHint' => false], $older['tools'][0]['annotations']);
        self::assertSame(['readOnlyHint' => false, 'destructiveHint' => true, 'idempotentHint' => false,
            'openWorldHint' => true], $older['tools'][1]['annotations'], 'the specification defaults');
        self::assertSame(['tools'], \array_keys($older));

        $current = self::decoded($this->current('tools/list'))['result'];
        self::assertSame(['resultType' => 'complete', 'tools' => $older['tools'], 'ttlMs' => 0,
            'cacheScope' => 'private', '_meta' => ['io.modelcontextprotocol/serverInfo' => ['name' => 'test-server',
                'version' => '1.2.3', 'title' => 'Test server']]], $current);
    }

    public function testServerDiscoverDescribesTheServer(): void
    {
        self::assertSame(['resultType' => 'complete', 'supportedVersions' => ['2026-07-28', '2025-11-25',
            '2025-06-18'], 'capabilities' => ['tools' => ['listChanged' => false]], 'instructions' => 'Use the tools',
            'ttlMs' => 0, 'cacheScope' => 'private', '_meta' => ['io.modelcontextprotocol/serverInfo' => [
                'name' => 'test-server', 'version' => '1.2.3', 'title' => 'Test server']]],
            self::decoded($this->current('server/discover'))['result']);
    }

    public function testTheCurrentEraChecksItsVersionCapabilitiesAndMirroredHeaders(): void
    {
        self::assertSame([400, -32022, ['supported' => ['2026-07-28', '2025-11-25', '2025-06-18'],
            'requested' => '2099-01-01']], self::error($this->current('tools/list', version: '2099-01-01')));
        $noCapabilities = $this->post(self::message('tools/list', ['_meta' => [
            'io.modelcontextprotocol/protocolVersion' => '2026-07-28']]),
            ['MCP-Protocol-Version' => '2026-07-28', 'Mcp-Method' => 'tools/list']);
        self::assertSame([400, -32602, null], self::error($noCapabilities));

        $mismatches = [['MCP-Protocol-Version' => '2025-11-25'], ['Mcp-Method' => 'tools/call'],
            ['Mcp-Method' => 'Tools/List']];
        foreach ($mismatches as $headers) {
            self::assertSame([400, -32020, null], self::error($this->current('tools/list', headers: $headers)));
        }

        $server = $this->server();
        $missing = new FakeRequest('POST', ['Authorization' => 'Bearer ' . self::TOKEN,
            'MCP-Protocol-Version' => '2026-07-28'], self::message('tools/list', ['_meta' => [
                'io.modelcontextprotocol/protocolVersion' => '2026-07-28',
                'io.modelcontextprotocol/clientCapabilities' => new \stdClass()]]));
        self::assertSame([400, -32020, null], self::error($server->handle($missing)), 'Mcp-Method missing');

        $call = ['name' => 'echo', 'arguments' => ['text' => 'ok']];
        self::assertSame([400, -32020, null], self::error($this->current('tools/call', $call,
            ['Mcp-Name' => 'refuse'])));
        self::assertFalse(self::decoded($this->current('tools/call', $call,
            ['Mcp-Name' => '=?base64?' . \base64_encode('echo') . '?=']))['result']['isError'], 'decoded Mcp-Name');
        self::assertCount(1, $this->calls, 'only the call whose header matched ran');
    }

    public function testMethodsOutsideTheCurrentEraAreNotFound(): void
    {
        foreach (['ping', 'initialize', 'resources/list'] as $method) {
            $response = $method === 'initialize'
                ? $this->post(self::message($method, ['protocolVersion' => '2026-07-28', '_meta' => [
                    'io.modelcontextprotocol/protocolVersion' => '2026-07-28']]))
                : $this->current($method);
            if ($method === 'initialize') {
                self::assertSame('2025-11-25', self::decoded($response)['result']['protocolVersion'],
                    'initialize always selects the older era');
                continue;
            }

            self::assertSame([404, -32601, null], self::error($response), $method);
        }
    }

    public function testToolsCallRunsTheHandlerWithCheckedArguments(): void
    {
        $result = self::decoded($this->current('tools/call', ['name' => 'echo', 'arguments' => ['text' => 'é€😀',
            'count' => 2, 'tags' => ['a']]]))['result'];
        self::assertSame(['resultType' => 'complete', 'content' => [['type' => 'text',
            'text' => '{"echo":{"text":"é€😀","count":2,"tags":["a"]}}']],
            'structuredContent' => ['echo' => ['text' => 'é€😀', 'count' => 2, 'tags' => ['a']]], 'isError' => false],
            \array_diff_key($result, ['_meta' => true]));
        self::assertEquals([[['text' => 'é€😀', 'count' => 2, 'tags' => ['a']], (object) ['user' => 7]]],
            $this->calls);

        $legacy = self::decoded($this->legacy('tools/call', ['name' => 'echo', 'arguments' => ['text' => 'a']]));
        self::assertSame(['content', 'structuredContent', 'isError'], \array_keys($legacy['result']));
    }

    public function testInvalidArgumentsAndRefusalsAreToolErrors(): void
    {
        $failures = [
            [['count' => 1], 'arguments.text is required'],
            [['text' => 'abcd'], 'arguments.text must be at most 3 characters long'],
            [['text' => 'a', 'count' => 0], 'arguments.count must be at least 1'],
            [['text' => 'a', 'count' => 1.5], 'arguments.count must be of type integer'],
            [['text' => 'a', 'tags' => ['x', 2]], 'arguments.tags[1] must be of type string'],
            [['text' => 'a', 'extra' => true], 'arguments.extra is not a known argument'],
        ];
        foreach ($failures as [$arguments, $message]) {
            self::assertSame(['content' => [['type' => 'text', 'text' => $message]], 'isError' => true],
                self::decoded($this->legacy('tools/call', ['name' => 'echo', 'arguments' => $arguments]))['result']);
        }

        self::assertSame([], $this->calls, 'no handler ran on invalid arguments');
        self::assertSame(['content' => [['type' => 'text', 'text' => 'Work not found']], 'isError' => true],
            self::decoded($this->legacy('tools/call', ['name' => 'refuse']))['result']);
        self::assertFalse(self::decoded($this->legacy('tools/call', ['name' => 'hidden']))['result']['isError'],
            'a tool left out of the list still runs: its handler checks rights');
    }

    public function testProtocolErrorsOfToolsCall(): void
    {
        self::assertSame([200, -32602, null], self::error($this->legacy('tools/call', ['name' => 'missing'])));
        self::assertSame([200, -32602, null], self::error($this->legacy('tools/call', ['name' => 'echo',
            'arguments' => ['a']])));
        $broken = $this->legacy('tools/call', ['name' => 'break']);
        self::assertSame([500, -32603, null], self::error($broken));
        self::assertStringNotContainsString('secret', (string) $broken->body);
        self::assertSame(['error break secret detail'], $this->logged);
    }

    public function testToolsAreCheckedWhenRegistered(): void
    {
        $handler = static fn(array $arguments, object $principal): array => [];
        $invalid = [
            ['bad name', ['type' => 'object']],
            ['ok', ['type' => 'string']],
            ['ok', ['type' => 'object', 'properties' => ['a' => ['type' => 'string', 'format' => 'email']]]],
            ['ok', ['type' => 'object', 'properties' => ['a' => ['oneOf' => []]]]],
            ['ok', ['type' => 'object', 'properties' => ['a' => ['type' => 'text']]]],
            ['ok', ['type' => 'object', 'properties' => ['a' => ['type' => 'string', 'pattern' => '(']]]],
        ];
        foreach ($invalid as [$name, $schema]) {
            try {
                new Tool($name, 'x', $schema, $handler);
                self::fail('accepted ' . \json_encode([$name, $schema]));
            } catch (\InvalidArgumentException) {
                // refused, as expected
            }
        }

        $registry = new ToolRegistry()->add(new Tool('one', 'x', ['type' => 'object'], $handler));
        $this->expectException(\InvalidArgumentException::class);
        $registry->add(new Tool('one', 'y', ['type' => 'object'], $handler));
    }

    public function testSchemaKeywords(): void
    {
        $schema = ['type' => 'object', 'properties' => [
            'state' => ['type' => 'string', 'enum' => ['public', 'hidden']],
            'id' => ['type' => 'string', 'pattern' => '^[a-z]+$'],
            'ratio' => ['type' => 'number', 'maximum' => 1],
            'list' => ['type' => 'array', 'minItems' => 1, 'maxItems' => 2],
            'maybe' => ['type' => ['string', 'null']],
        ]];
        $cases = [
            ['{"state":"hidden"}', null],
            ['{"state":"gone"}', 'arguments.state must be one of ["public","hidden"]'],
            ['{"id":"abc"}', null],
            ['{"id":"ab1"}', 'arguments.id does not match the pattern ^[a-z]+$'],
            ['{"ratio":1}', null],
            ['{"ratio":0.5}', null],
            ['{"ratio":1.5}', 'arguments.ratio must be at most 1'],
            ['{"list":[]}', 'arguments.list must have at least 1 items'],
            ['{"list":[1,2,3]}', 'arguments.list must have at most 2 items'],
            ['{"list":{}}', 'arguments.list must be of type array'],
            ['{"maybe":null}', null],
            ['{"maybe":false}', 'arguments.maybe must be of type string or null'],
        ];
        foreach ($cases as [$json, $expected]) {
            self::assertSame($expected, SchemaValidator::check($schema, \json_decode($json)), $json);
        }
    }
}
