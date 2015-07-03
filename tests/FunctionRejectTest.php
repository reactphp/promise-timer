<?php

use Clue\Promise\Timer;
use React\Promise\CancellablePromiseInterface;

class FunctionRejectTest extends TestCase
{
    public function testPromiseIsPendingWithoutRunningLoop()
    {
        $promise = Timer\reject(0.01, $this->loop);

        $this->expectPromisePending($promise);
    }

    public function testPromiseWillBeRejectedOnTimeout()
    {
        $promise = Timer\reject(0.01, $this->loop);

        $this->loop->run();

        $this->expectPromiseRejected($promise);
    }

    public function testCancelingPromiseWillRejectTimer()
    {
        $promise = Timer\reject(0.01, $this->loop);

        if (!($promise instanceof CancellablePromiseInterface)) {
            $this->markTestSkipped('Outdated Promise API');
        }

        $promise->cancel();

        $this->expectPromiseRejected($promise);
    }
}
