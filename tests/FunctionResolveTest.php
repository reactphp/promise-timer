<?php

use Clue\Promise\Timeout;

class FunctionResolveTest extends TestCase
{
    public function testPromiseIsPendingWithoutRunningLoop()
    {
        $promise = Timeout\resolve(0.01, $this->loop);

        $this->expectPromisePending($promise);
    }

    public function testPromiseWillBeResolvedOnTimeout()
    {
        $promise = Timeout\resolve(0.01, $this->loop);

        $this->loop->run();

        $this->expectPromiseResolved($promise);
    }
}
