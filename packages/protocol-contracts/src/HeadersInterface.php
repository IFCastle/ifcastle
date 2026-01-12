<?php

declare(strict_types=1);

namespace IfCastle\Protocol;

interface HeadersInterface
{
    public const string ACCEPT             = 'Accept';

    public const string ACCEPT_CHARSET     = 'Accept-Charset';

    public const string ACCEPT_ENCODING    = 'Accept-Encoding';

    public const string ACCEPT_LANGUAGE    = 'Accept-Language';

    public const string ACCEPT_RANGES      = 'Accept-Ranges';

    public const string ACCESS_CONTROL     = 'Access-Control';

    public const string CONTENT_TYPE       = 'Content-Type';

    public const string AUTHORIZATION      = 'Authorization';

    public const string CACHE_CONTROL      = 'Cache-Control';

    public const string CONNECTION         = 'Connection';

    public const string COOKIE             = 'Cookie';

    public const string CONTENT_LENGTH     = 'Content-Length';

    public const string CONTENT_MD5        = 'Content-MD5';

    public const string CONTENT_RANGE      = 'Content-Range';

    public const string MIME_FORM_URLENCODED = 'application/x-www-form-urlencoded';

    public const string MIME_MULTIPART_FORM_DATA = 'multipart/form-data';

    public const string MIME_APPLICATION_JSON = 'application/json';

    public const string MIME_APPLICATION_XML = 'application/xml';

    public const string MIME_APPLICATION_OCTET_STREAM = 'application/octet-stream';

    public const string MIME_TEXT_PLAIN = 'text/plain';

    public const string MIME_TEXT_HTML = 'text/html';

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHeaders(): array;

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return bool Returns true if any header names match the given header
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching header name is found in the message.
     */
    public function hasHeader(string $name): bool;

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    public function getHeader(string $name): array;

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $name Case-insensitive header field name.
     * @return string A string of values as provided for the given header
     *    concatenated together using a comma. If the header does not appear in
     *    the message, this method MUST return an empty string.
     */
    public function getHeaderLine(string $name): string;
}
