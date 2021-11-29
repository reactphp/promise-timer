<?php

namespace React\Promise\Timer;

use RuntimeException;

class TimeoutException extends RuntimeException
{
    private $timeout;

    public function __construct($timeout, $message = null, $code = null, $previous = null)
    {
        // Preserve compatibility with our former signature, but avoid invalid arguments for the parent constructor:
        if ($message === null) {
            $message = '';
        }
        if ($code === null) {
            $code = 0;
        }
        parent::__construct($message, $code, $previous);

        $this->timeout = $timeout;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }
}
