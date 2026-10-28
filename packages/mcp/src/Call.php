<?php

declare(strict_types=1);

namespace IfCastle\Mcp;

/** One JSON-RPC request or notification as McpServer dispatches it. */
final readonly class Call
{
    /**
     * @param string|int|null $id null for a notification
     * @param bool $notification the message carries no id, so it is answered 202 with no body
     */
    public function __construct(
        public string $method,
        public \stdClass $params,
        public string|int|null $id,
        public bool $notification,
    ) {}
}
