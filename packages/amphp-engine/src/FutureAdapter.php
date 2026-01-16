<?php

declare(strict_types=1);

namespace IfCastle\Amphp;

use Amp\Future;
use IfCastle\Async\CancellationInterface;
use IfCastle\Async\FutureInterface;

/**
 * @template T
 * @implements FutureInterface<T>
 */
final readonly class FutureAdapter implements FutureInterface
{
    /**
     * @param Future<T> $future
     */
    public function __construct(
        /** @var Future<T> */
        public Future $future
    ) {}


    #[\Override]
    public function isComplete(): bool
    {
        return $this->future->isComplete();
    }

    #[\Override]
    public function ignore(): void
    {
        $this->future->ignore();
    }

    #[\Override]
    /**
     * @return T
     */
    public function await(?CancellationInterface $cancellation = null): mixed
    {
        return $this->future->await();
    }

    #[\Override]
    /**
     * @template TReturn
     * @param callable(T): TReturn $mapper
     * @return FutureInterface<TReturn>
     */
    public function map(callable $mapper): FutureInterface
    {
        /** @phpstan-ignore-next-line */
        return new FutureAdapter($this->future->map($mapper));
    }

    #[\Override]
    /**
     * @param callable(\Throwable): T $onRejected
     */
    public function catch(callable $onRejected): static
    {
        /** @phpstan-ignore-next-line */
        $this->future->catch($onRejected)->ignore();
        return $this;
    }

    #[\Override]
    public function finally(callable $onFinally): static
    {
        $this->future->finally($onFinally)->ignore();
        return $this;
    }
}
