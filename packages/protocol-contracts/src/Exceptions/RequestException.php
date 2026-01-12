<?php

declare(strict_types=1);

namespace IfCastle\Protocol\Exceptions;

use IfCastle\Exceptions\BaseException;
use IfCastle\Exceptions\ClientAvailableInterface;

class RequestException extends BaseException implements ProblemDetailsInterface, ClientAvailableInterface, HttpErrorInterface
{
    protected array $tags           = ['request'];

    /**
     * @param array<string, mixed> $additionalData
     * @param array<string, array{pointer: string, detail: string}> $errors
     */
    public function __construct(
        string                  $message,
        int                     $status         = 500,
        string                  $type           = '',
        string                  $detail         = '',
        ?string                 $instance       = null,
        array                   $errors         = [],
        array                   $additionalData = [],
        ?\Throwable             $previous       = null,
    ) {
        parent::__construct([
            'message' => $detail,
            ProblemDetailsInterface::TYPE      => $type,
            ProblemDetailsInterface::TITLE     => $message,
            ProblemDetailsInterface::STATUS    => $status,
            ProblemDetailsInterface::DETAIL    => $detail,
            ProblemDetailsInterface::INSTANCE  => $instance,
            ProblemDetailsInterface::ERRORS    => $errors,
        ] + $additionalData, $status, $previous);
    }

    #[\Override]
    public function getClientMessage(): string
    {
        return $this->getMessage();
    }

    #[\Override]
    public function clientSerialize(): array
    {
        return $this->data;
    }

    #[\Override]
    public function getStatusCode(): int
    {
        return $this->getCode();
    }

    #[\Override]
    public function getReasonPhrase(): string|null
    {
        return $this->getTitle();
    }

    #[\Override]
    public function getType(): string
    {
        return $this->data[ProblemDetailsInterface::TYPE] ?? '';
    }

    #[\Override]
    public function getTitle(): string
    {
        return $this->data[ProblemDetailsInterface::TITLE] ?? $this->getMessage();
    }

    #[\Override]
    public function getStatus(): int
    {
        return $this->getCode();
    }

    #[\Override]
    public function getDetail(): ?string
    {
        return $this->data[ProblemDetailsInterface::DETAIL] ?? null;
    }

    #[\Override]
    public function getInstance(): ?string
    {
        return $this->data[ProblemDetailsInterface::INSTANCE] ?? null;
    }

    #[\Override]
    public function getErrors(): array
    {
        /* @phpstan-ignore-next-line */
        return $this->data[ProblemDetailsInterface::ERRORS] ?? [];
    }

    public function addError(string $pointer, string $detail): static
    {
        $this->data[ProblemDetailsInterface::ERRORS][] = [
            'pointer' => $pointer,
            'detail'  => $detail,
        ];

        return $this;
    }

    #[\Override]
    public function getAdditionalData(): array
    {
        return $this->data;
    }
}
