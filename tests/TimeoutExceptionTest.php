<?php

namespace React\Tests\Promise\Timer;

use React\Promise\Timer\TimeoutException;

class TimeoutExceptionTest extends TestCase
{
    public function testCtorWithAllParameters()
    {
        $previous = new \Exception();
        $e = new TimeoutException(1.0, 'Error', 42, $previous);

        $this->assertEquals(1.0, $e->getTimeout());
        $this->assertEquals('Error', $e->getMessage());
        $this->assertEquals(42, $e->getCode());
        $this->assertSame($previous, $e->getPrevious());
    }

    public function testCtorWithDefaultValues()
    {
        $e = new TimeoutException(2.0);

        $this->assertEquals(2.0, $e->getTimeout());
        $this->assertEquals('', $e->getMessage());
        $this->assertEquals(0, $e->getCode());
        $this->assertNull($e->getPrevious());
    }

    public function testCtorWithIntTimeoutWillBeReturnedAsFloat()
    {
        $e = new TimeoutException(1);

        $this->assertSame(1.0, $e->getTimeout());
    }

    public function testLegacyCtorWithNullValues()
    {
        $e = new TimeoutException(10, null, null, null);

        $this->assertEquals(10.0, $e->getTimeout());
        $this->assertEquals('', $e->getMessage());
        $this->assertEquals(0, $e->getCode());
        $this->assertNull($e->getPrevious());
    }
}
