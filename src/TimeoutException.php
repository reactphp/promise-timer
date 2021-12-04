<?php

namespace React\Promise\Timer;

use RuntimeException;

class TimeoutException extends RuntimeException
{
    /** @var float */
    private $timeout;

    /**
     * @param float                      $timeout
     * @param ?string                    $message
     * @param ?int                       $code
     * @param null|\Exception|\Throwable $previous
     */
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

    /**
     * Get the timeout value in seconds.
     *
     * @return float
     */
    public function getTimeout()
    {
        return $this->timeout;
    }
}
