<?php

declare(strict_types=1);

namespace IfCastle\TrueAsync;

use PHPUnit\Framework\TestCase;

use function Async\await;
use function Async\spawn;

class CoroutineContextTest extends TestCase
{
    private CoroutineContext $context;

    private CoroutineScheduler $scheduler;

    #[\Override]
    protected function setUp(): void
    {
        $this->context              = new CoroutineContext();
        $this->scheduler            = new CoroutineScheduler();
    }

    public function testValuesAreIsolatedBetweenSiblings(): void
    {
        $first                      = spawn(function (): mixed {
            $this->context->set('key', 'first');
            \Async\delay(5);
            return $this->context->get('key');
        });

        $second                     = spawn(function (): mixed {
            $this->context->set('key', 'second');
            return $this->context->get('key');
        });

        $this->assertSame('first', await($first));
        $this->assertSame('second', await($second));
    }

    public function testChildStartedByRunInheritsParentValues(): void
    {
        $result                     = null;

        $parent                     = spawn(function () use (&$result): void {
            $this->context->set('user', 'alice');
            $child                  = $this->scheduler->run(function () use (&$result): void {
                $result             = [
                    $this->context->get('user'),
                    $this->context->has('user'),
                    $this->context->getLocal('user'),
                    $this->context->hasLocal('user'),
                ];
            });
            Support::waitUntilFinished($child);
        });

        await($parent);

        $this->assertSame(['alice', true, null, false], $result);
    }

    public function testChildValueShadowsParentWithoutChangingIt(): void
    {
        $seenByParent               = null;

        await(spawn(function () use (&$seenByParent): void {
            $this->context->set('user', 'alice');
            Support::waitUntilFinished($this->scheduler->run(function (): void {
                $this->context->set('user', 'bob');
            }));
            $seenByParent           = $this->context->get('user');
        }));

        $this->assertSame('alice', $seenByParent);
    }

    public function testDirectSpawnDoesNotInherit(): void
    {
        $result                     = await(spawn(function (): mixed {
            $this->context->set('user', 'alice');
            return await(spawn(fn(): bool => $this->context->has('user')));
        }));

        $this->assertFalse($result);
    }

    public function testParentId(): void
    {
        $ids                        = await(spawn(function (): array {
            $parentId               = $this->context->getCoroutineId();
            $childParentId          = null;
            Support::waitUntilFinished($this->scheduler->run(function () use (&$childParentId): void {
                $childParentId      = $this->context->getCoroutineParentId();
            }));

            return [$parentId, $childParentId, $this->context->getCoroutineParentId()];
        }));

        $this->assertSame($ids[0], $ids[1]);
        $this->assertSame(-1, $ids[2]);
    }

    public function testDeferRunsOnCompletionAndOnCancellation(): void
    {
        $calls                      = [];

        await(spawn(function () use (&$calls): void {
            $this->context->defer(function () use (&$calls): void {
                $calls[]            = 'completed';
            });
        }));

        $cancelled                  = spawn(function () use (&$calls): void {
            $this->context->defer(function () use (&$calls): void {
                $calls[]            = 'cancelled';
            });
            \Async\delay(1000);
        });

        \Async\delay(5);
        $cancelled->cancel();

        try {
            await($cancelled);
        } catch (\Cancellation) {
        }

        $this->assertSame(['completed', 'cancelled'], $calls);
    }

    public function testDeferCallbackErrorIsReported(): void
    {
        $logger                     = Support::collectingLogger();
        $context                    = new CoroutineContext($logger);

        await(spawn(static function () use ($context): void {
            $context->defer(static function (): void {
                throw new \RuntimeException('deferred boom');
            });
        }));

        \Async\delay(5);

        $this->assertSame(['error: deferred boom'], $logger->messages);
    }

    public function testGetFallsBackToScopeContext(): void
    {
        $key                        = 'scope-key-' . \uniqid();
        \Async\current_context()->set($key, 'from-scope');

        try {
            $seen                   = await(spawn(fn(): array => [$this->context->get($key), $this->context->has($key)]));
        } finally {
            \Async\current_context()->unset($key);
        }

        $this->assertSame(['from-scope', true], $seen);
    }
}
