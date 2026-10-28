# mcp

A stateless [Model Context Protocol](https://modelcontextprotocol.io) server over Streamable HTTP for IFCastle
applications: tools only, every answer `application/json`, no sessions.

It serves the current revision, 2026-07-28 (per-request `_meta`, `server/discover`, mirrored headers), and the older
era of 2025-11-25 and 2025-06-18 (`initialize` without a session id) at the same endpoint, choosing the era per
request by its body. The host application supplies:

- a `BearerAuthenticatorInterface`, which turns `Authorization: Bearer <token>` into the principal tools run as;
- a `ToolRegistry` of `Tool` definitions, each with a JSON Schema (the subset `SchemaValidator` checks) and a handler;
- the `Origin` values a browser may send.

```php
$server = new McpServer(new ServerInfo('my-app', '1.0'), $tools, $authenticator, ['https://my-app.example']);
$response = $server->handle($httpRequest);   // status, headers, body to send as they are
```
