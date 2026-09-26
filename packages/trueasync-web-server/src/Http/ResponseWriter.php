<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer\Http;

use IfCastle\Async\ReadableStreamInterface;
use IfCastle\Protocol\Http\HttpResponseInterface;
use TrueAsync\HttpResponse;

/**
 * Copies an IFCastle HTTP response into the TrueAsync\HttpResponse the server will send.
 */
final class ResponseWriter
{
    /**
     * Sets status, reason phrase and headers, then the body. A string body is sent when the handler
     * returns; a stream is written chunk by chunk as it is read and the response is ended with it,
     * so nothing may be written to $target afterwards. A stream that throws aborts the response and
     * the exception is rethrown.
     */
    public function write(HttpResponseInterface $response, HttpResponse $target): void
    {
        $target->setStatusCode($response->getStatusCode());

        if ($response->getReasonPhrase() !== '') {
            $target->setReasonPhrase($response->getReasonPhrase());
        }

        foreach ($response->getHeaders() as $name => $values) {
            $target->setHeader($name, $values);
        }

        $body                       = $response->getBody();

        if (\is_string($body)) {
            $target->setBody($body);
            return;
        }

        $this->writeStream($body, $target);
    }

    private function writeStream(ReadableStreamInterface $body, HttpResponse $target): void
    {
        try {
            while (($chunk = $body->read()) !== null) {
                $target->write($chunk);
            }
        } catch (\Throwable $exception) {
            // The headers may be on the wire already: fail the response so the client cannot take
            // a cut body for a whole one, whatever the caller does with the exception.
            $target->abort();

            throw $exception;
        } finally {
            $body->close();
        }

        $target->end();
    }
}
