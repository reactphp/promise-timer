<?php

use Clue\Promise\Timer;

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
}
