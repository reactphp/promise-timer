<?php

use Clue\Promise\Timeout;
use React\Promise;

class FunctionTimeoutTest extends TestCase
{
    public function testResolvedWillResolveRightAway()
    {
        $promise = Promise\resolve();

        $promise = Timeout\timeout($promise, 3, $this->loop);

        $this->expectPromiseResolved($promise);
    }

    public function testResolvedWillNotStartTimer()
    {
        $promise = Promise\resolve();

        Timeout\timeout($promise, 3, $this->loop);

        $time = microtime(true);
        $this->loop->run();
        $time = microtime(true) - $time;

        $this->assertLessThan(0.5, $time);
    }

    public function testRejectedWillRejectRightAway()
    {
        $promise = Promise\reject();

        $promise = Timeout\timeout($promise, 3, $this->loop);

        $this->expectPromiseRejected($promise);
    }

    public function testRejectedWillNotStartTimer()
    {
        $promise = Promise\reject();

        Timeout\timeout($promise, 3, $this->loop);

        $time = microtime(true);
        $this->loop->run();
        $time = microtime(true) - $time;

        $this->assertLessThan(0.5, $time);
    }

    public function testPendingWillRejectOnTimeout()
    {
        $promise = $this->getMock('React\Promise\PromiseInterface');

        $promise = Timeout\timeout($promise, 0.01, $this->loop);

        $this->loop->run();

        $this->expectPromiseRejected($promise);
    }

    public function testPendingCancellableWillBeCancelledOnTimeout()
    {
        if (!interface_exists('React\Promise\CancellablePromiseInterface', true)) {
            $this->markTestSkipped('Your (outdated?) Promise API does not support cancellable promises');
        }

        $promise = $this->getMock('React\Promise\CancellablePromiseInterface');
        $promise->expects($this->once())->method('cancel');


        Timeout\timeout($promise, 0.01, $this->loop);

        $this->loop->run();
    }
}
