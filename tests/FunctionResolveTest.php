<?php

use React\Promise\Timer;
use React\Promise\CancellablePromiseInterface;

class FunctionResolveTest extends TestCase
{
    public function testPromiseIsPendingWithoutRunningLoop()
    {
        $promise = Timer\resolve(0.01, $this->loop);

        $this->expectPromisePending($promise);
    }

    public function testPromiseWillBeResolvedOnTimeout()
    {
        $promise = Timer\resolve(0.01, $this->loop);

        $this->loop->run();

        $this->expectPromiseResolved($promise);
    }

    public function testWillStartLoopTimer()
    {
        $loop = $this->getMock('React\EventLoop\LoopInterface');
        $loop->expects($this->once())->method('addTimer')->with($this->equalTo(0.01));

        Timer\resolve(0.01, $loop);
    }

    public function testCancellingPromiseWillCancelLoopTimer()
    {
        $loop = $this->getMock('React\EventLoop\LoopInterface');

        $timer = $this->getMock('React\EventLoop\Timer\TimerInterface');
        $loop->expects($this->once())->method('addTimer')->will($this->returnValue($timer));

        $promise = Timer\resolve(0.01, $loop);

        if (!($promise instanceof CancellablePromiseInterface)) {
            $this->markTestSkipped('Outdated Promise API');
        }

        $loop->expects($this->once())->method('cancelTimer')->with($this->equalTo($timer));

        $promise->cancel();
    }

    public function testCancelingPromiseWillRejectTimer()
    {
        $promise = Timer\resolve(0.01, $this->loop);

        if (!($promise instanceof CancellablePromiseInterface)) {
            $this->markTestSkipped('Outdated Promise API');
        }

        $promise->cancel();

        $this->expectPromiseRejected($promise);
    }
}
