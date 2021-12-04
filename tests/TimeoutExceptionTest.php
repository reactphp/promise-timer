<?php

namespace React\Tests\Promise\Timer;

use ErrorException;
use React\Promise\Timer\TimeoutException;

class TimeoutExceptionTest extends TestCase
{
    public function testAccessTimeout()
    {
        $e = new TimeoutException(10);

        $this->assertEquals(10, $e->getTimeout());
    }

    public function testEnsureNoDeprecationsAreTriggered()
    {
        $formerReporting = error_reporting();
        error_reporting(E_ALL | E_STRICT);
        $this->setStrictErrorHandling();

        try {
            $e = new TimeoutException(10);
        } catch (ErrorException $e) {
            error_reporting($formerReporting);
            throw $e;
        }

        error_reporting($formerReporting);
        $this->assertEquals(10, $e->getTimeout());
    }

    protected function setStrictErrorHandling()
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            if (! (error_reporting() & $errno)) {
                return false;
            }
            switch ($errno) {
                case E_DEPRECATED:
                    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            }

            return false;
        });
    }
}
