<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer\Http;

use IfCastle\Async\ReadableStreamInterface;
use IfCastle\Protocol\Exceptions\ParseException;
use IfCastle\Protocol\Exceptions\RequestException;
use IfCastle\Protocol\FileContainerInterface;
use IfCastle\Protocol\HeadersInterface;
use IfCastle\Protocol\Http\HttpRequestForm;
use IfCastle\Protocol\Http\HttpRequestInterface;
use League\Uri\Http;
use Psr\Http\Message\UriInterface;
use TrueAsync\HttpException;
use TrueAsync\HttpRequest;

/**
 * A TrueAsync\HttpRequest seen as an IFCastle HTTP request.
 *
 * The body is read whole: the server buffers it before the handler runs. Request parameters are
 * the query parameters; form fields and uploaded files come from retrieveRequestForm().
 *
 * A body or form the server refused (past a limit, malformed) throws RequestException with the
 * server's status, 400 or 413, so the pipeline answers with it rather than with 500.
 */
final class HttpRequestAdapter implements HttpRequestInterface
{
    private const array FORM_MEDIA_TYPES = [
        HeadersInterface::MIME_FORM_URLENCODED,
        HeadersInterface::MIME_MULTIPART_FORM_DATA,
    ];

    private ?UriInterface $uri      = null;

    /**
     * @var array<string, string>|null
     */
    private ?array $cookies         = null;

    private HttpRequestForm|false|null $form = null;

    /**
     * @param string $scheme "http" or "https": the request itself does not say which listener took it.
     */
    public function __construct(
        private readonly HttpRequest $request,
        private readonly string $scheme = 'http'
    ) {}

    /**
     * @throws ParseException when the Host header does not form a valid URI
     */
    #[\Override]
    public function getUri(): UriInterface
    {
        if ($this->uri !== null) {
            return $this->uri;
        }

        $target                     = $this->request->getUri();

        // An origin-form target ("/path?query") takes its authority from Host; an absolute-form
        // one (RFC 9112 §3.2.2) carries its own.
        if (\str_starts_with($target, '/')) {
            $target                 = $this->scheme . '://' . ($this->request->getHeader('host') ?? 'localhost') . $target;
        }

        try {
            $this->uri              = Http::new($target);
        } catch (\Throwable $exception) {
            throw new ParseException('Invalid request URI or Host header', 0, $exception);
        }

        return $this->uri;
    }

    #[\Override]
    public function getMethod(): string
    {
        return $this->request->getMethod();
    }

    /**
     * Cookies from the Cookie header, values URL-decoded; the first of two equal names wins, as in PHP.
     */
    #[\Override]
    public function getCookies(): array
    {
        if ($this->cookies !== null) {
            return $this->cookies;
        }

        $this->cookies              = [];

        foreach (\explode(';', $this->request->getHeaderLine('cookie')) as $pair) {
            [$name, $value]         = \array_pad(\explode('=', $pair, 2), 2, '');
            $name                   = \trim($name);

            if ($name !== '') {
                $this->cookies[$name] ??= \urldecode(\trim($value));
            }
        }

        return $this->cookies;
    }

    /**
     * Size of the body the client sent. The server keeps a multipart body as its parts, not its
     * bytes, so for one the declared Content-Length is the answer, and 0 when none was declared.
     */
    #[\Override]
    public function getBodySize(): int
    {
        $body                       = self::read($this->request->getBody(...));

        return $body !== '' ? \strlen($body) : ($this->request->getContentLength() ?? 0);
    }

    #[\Override]
    public function getBody(): string
    {
        return self::read($this->request->getBody(...));
    }

    #[\Override]
    public function getBodyStream(): ?ReadableStreamInterface
    {
        return null;
    }

    #[\Override]
    public function retrieveRequestForm(): HttpRequestForm|null
    {
        if ($this->form === null) {
            $mediaType              = \strtolower(\trim(\explode(';', $this->request->getContentType() ?? '', 2)[0]));

            $this->form             = \in_array($mediaType, self::FORM_MEDIA_TYPES, true)
                                      ? new HttpRequestForm(
                                          $this->request->getQuery(),
                                          self::read($this->request->getPost(...)),
                                          UploadedFileContainer::fromFiles(self::read($this->request->getFiles(...)))
                                      )
                                      : false;
        }

        return $this->form === false ? null : $this->form;
    }

    /**
     * Header names are lower-case, as the server reports them; each header has one value.
     */
    #[\Override]
    public function getHeaders(): array
    {
        return \array_map(static fn(string $value): array => [$value], $this->request->getHeaders());
    }

    #[\Override]
    public function hasHeader(string $name): bool
    {
        return $this->request->hasHeader($name);
    }

    /**
     * One element at most: a header sent several times arrives joined with ", " by the server,
     * and splitting it back is unsafe for values that hold commas themselves (dates).
     */
    #[\Override]
    public function getHeader(string $name): array
    {
        $value                      = $this->request->getHeader($name);

        return $value === null ? [] : [$value];
    }

    #[\Override]
    public function getHeaderLine(string $name): string
    {
        return $this->request->getHeaderLine($name);
    }

    #[\Override]
    public function getRequestParameters(): array
    {
        return $this->request->getQuery();
    }

    #[\Override]
    public function getRequestParameter(string $name): mixed
    {
        return $this->request->getQueryParam($name);
    }

    #[\Override]
    public function requestParameters(string ...$names): array
    {
        return \array_intersect_key($this->request->getQuery(), \array_flip($names));
    }

    #[\Override]
    public function requestParametersWithNull(string ...$names): array
    {
        $query                      = $this->request->getQuery();
        $result                     = [];

        foreach ($names as $name) {
            $result[$name]          = $query[$name] ?? null;
        }

        return $result;
    }

    #[\Override]
    public function isRequestParametersExist(string ...$names): bool
    {
        $query                      = $this->request->getQuery();

        foreach ($names as $name) {
            if (false === \array_key_exists($name, $query)) {
                return false;
            }
        }

        return true;
    }

    #[\Override]
    public function isRequestParametersDefined(string ...$names): bool
    {
        $query                      = $this->request->getQuery();

        foreach ($names as $name) {
            if (($query[$name] ?? null) === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Uploaded files with a field of their own; files sent as a list (photos[]) are in the form.
     */
    #[\Override]
    public function getUploadedFiles(): array
    {
        return \array_filter(
            $this->retrieveRequestForm()?->files ?? [],
            static fn(mixed $file): bool => $file instanceof FileContainerInterface
        );
    }

    #[\Override]
    public function getUploadedFile(string $name): ?FileContainerInterface
    {
        return $this->getUploadedFiles()[$name] ?? null;
    }

    #[\Override]
    public function hasUploadedFile(string $name): bool
    {
        return $this->getUploadedFile($name) !== null;
    }

    /**
     * @template T
     *
     * @param \Closure(): T $read a getter of the request that reads the body
     *
     * @return T
     *
     * @throws RequestException when the server refused the body or the form
     */
    private static function read(\Closure $read): mixed
    {
        try {
            return $read();
        } catch (HttpException $exception) {
            throw new RequestException($exception->getMessage(), $exception->getCode(), previous: $exception);
        }
    }
}
