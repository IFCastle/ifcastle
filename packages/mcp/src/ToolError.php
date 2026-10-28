<?php

declare(strict_types=1);

namespace IfCastle\Mcp;

/**
 * A refusal a tool reports to the model: a business or validation error, answered as a tool result with
 * `isError: true` and the message as its text, so the model can read it and retry. The message leaves the server.
 */
final class ToolError extends \RuntimeException {}
