<?php

declare(strict_types=1);

namespace IfCastle\Amphp;

use PHPUnit\Framework\TestCase;

use function Amp\async;
use function Amp\Future\await;
use function Amp\Sync\createChannelPair;

class CoroutineContextTest extends TestCase
{
    public function testContextBasicCase(): void
    {
        [$left, $right]             = createChannelPair();
        $context                    = new CoroutineContext();

        $future1                    = async(function () use ($left, $context) {
            $value                  = $context->get('future') ?? 'No value';
            $context->set('future', 'World1');
            $left->send('Hello ' . $value);

            $context->set('future', 'World2');
            $left->send('Hello ' . $context->get('future'));
        });

        $value                      = 'default';

        $future2                    = async(function () use ($right, $context, &$value) {
            $value                  = $context->get('future') ?? 'No value future2';
            $received               = $right->receive();
            $context->set('future', 'Future2');
            $this->assertSame('Hello No value', $received);

            $received               = $right->receive();
            $this->assertSame('Hello World2', $received);
            $this->assertSame('Future2', $context->get('future'));
        });

        await([$future1, $future2]);

        $this->assertSame('No value future2', $value);
    }

    public function testDispose(): void
    {
        [$left1, $right1]           = createChannelPair();
        [$left2, $right2]           = createChannelPair();
        $context                    = new CoroutineContext();
        $disposeCalled1             = false;
        $disposeCalled2             = false;

        $future1                    = async(function () use ($right1, $context, &$disposeCalled1) {
            $right1->receive();
            $context->defer(static function () use (&$disposeCalled1) {
                $disposeCalled1     = true;
            });
        });

        $future2                    = async(function () use ($right2, $context, &$disposeCalled2) {
            $right2->receive();
            $context->defer(static function () use (&$disposeCalled2) {
                $disposeCalled2     = true;
            });
        });

        $left1->send('Hello');
        await([$future1]);

        $this->assertTrue($disposeCalled1);

        $left2->send('Hello');
        await([$future2]);

        $this->assertTrue($disposeCalled2);
    }
}
