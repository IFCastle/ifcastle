<?php

declare(strict_types=1);

namespace IfCastle\Protocol\Exceptions;

/**
 * The HTTP 400 Bad Request client error response status code indicates that the server would not process
 * the request due to something the server considered to be a client error.
 * The reason for a 400 response is typically due to malformed request syntax,
 * invalid request message framing, or deceptive request routing.
 */
final class BadRequest extends RequestException
{
    public function __construct(
        string      $message        = 'Invalid Request',
        string      $type           = '',
        string      $detail         = 'The provided input data is not valid.',
        ?string     $instance       = null,
        array       $additionalData = [],
        ?\Throwable $previous       = null
    ) {
        parent::__construct($message, 400, $type, $detail, $instance, [], $additionalData, $previous);
    }
}
