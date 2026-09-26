<?php

declare(strict_types=1);

namespace IfCastle\TrueAsync;

use Async\AsyncException;
use Async\Context;
use IfCastle\Async\CoroutineContextInterface;
use IfCastle\TrueAsync\Internal\ErrorReporter;
use IfCastle\TrueAsync\Internal\ParentLink;
use Psr\Log\LoggerInterface;

use function Async\coroutine_context;
use function Async\current_context;
use function Async\current_coroutine;
use function Async\request_context;

/**
 * Coroutine-local storage with inheritance from the coroutine that started this one.
 *
 * Values set here are visible to the current coroutine and to the coroutines it starts through
 * CoroutineScheduler::run(). Lookup order: the current coroutine, its run() ancestors, then the
 * context of the current Scope and its parents, which under TrueAsync\HttpServer includes the
 * request. Coroutines started with Async\spawn() directly have no ancestors and see their own
 * values and the Scope context only.
 *
 * Every method except isCoroutine() and getCoroutineId() starts the TrueAsync scheduler if it has
 * not started yet.
 */
final readonly class CoroutineContext implements CoroutineContextInterface
{
    private ErrorReporter $errorReporter;

    /**
     * @param LoggerInterface|null $logger Receives errors thrown by defer() callbacks; without it they go to error_log().
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->errorReporter        = new ErrorReporter($logger);
    }

    /**
     * @return bool false only before the TrueAsync scheduler has started; afterwards the main
     *              script runs as a coroutine too.
     */
    #[\Override]
    public function isCoroutine(): bool
    {
        return $this->findCoroutineId() !== -1;
    }

    /**
     * @return int -1 before the TrueAsync scheduler has started.
     */
    #[\Override]
    public function getCoroutineId(): int
    {
        return $this->findCoroutineId();
    }

    /**
     * @return int -1 when the coroutine was not started by CoroutineScheduler::run().
     */
    #[\Override]
    public function getCoroutineParentId(): int
    {
        return $this->findParentLink(coroutine_context())->coroutineId ?? -1;
    }

    #[\Override]
    public function has(string $key): bool
    {
        return $this->findOwner($key) !== null || current_context()->has($key);
    }

    /**
     * @return mixed null when neither the coroutine chain nor the Scope context has the key.
     */
    #[\Override]
    public function get(string $key): mixed
    {
        $owner                      = $this->findOwner($key);

        return $owner !== null ? $owner->getLocal($key) : current_context()->find($key);
    }

    #[\Override]
    public function getLocal(string $key): mixed
    {
        return coroutine_context()->findLocal($key);
    }

    #[\Override]
    public function hasLocal(string $key): bool
    {
        return coroutine_context()->hasLocal($key);
    }

    #[\Override]
    public function set(string $key, mixed $value): static
    {
        coroutine_context()->set($key, $value, replace: true);

        return $this;
    }

    /**
     * Reads the context of the request Scope TrueAsync\HttpServer gives every request: one lookup,
     * whatever coroutine of the request asks.
     */
    #[\Override]
    public function getForRequest(string $key): mixed
    {
        $requestContext             = request_context();

        return $requestContext !== null ? $requestContext->findLocal($key) : $this->get($key);
    }

    #[\Override]
    public function setForRequest(string $key, mixed $value): static
    {
        $requestContext             = request_context();

        if ($requestContext === null) {
            $this->set($key, $value);
        } else {
            $requestContext->set($key, $value, replace: true);
        }

        return $this;
    }

    /**
     * Runs the callback when the current coroutine completes, including by cancellation.
     *
     * TrueAsync runs completion callbacks in a separate coroutine, so inside the callback
     * getCoroutineId() and get() refer to that coroutine, not to the completed one.
     * An error thrown by the callback is reported and does not propagate.
     */
    #[\Override]
    public function defer(callable $callback): static
    {
        $errorReporter              = $this->errorReporter;
        current_coroutine()->finally(static fn() => $errorReporter->call($callback));

        return $this;
    }

    private function findCoroutineId(): int
    {
        try {
            return current_coroutine()->getId();
        } catch (AsyncException) {
            return -1;
        }
    }

    /**
     * The context of the nearest coroutine in the run() chain that holds the key.
     */
    private function findOwner(string $key): ?Context
    {
        for ($context = coroutine_context(); $context !== null; $context = $this->findParentLink($context)?->context) {
            if ($context->hasLocal($key)) {
                return $context;
            }
        }

        return null;
    }

    private function findParentLink(Context $context): ?ParentLink
    {
        return $context->findLocal(ParentLink::key());
    }
}
