<?php

namespace Clue\Promise\Timeout;

use React\Promise\Deferred;
use React\Promise\CancellablePromiseInterface;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;

function await(PromiseInterface $promise, $time, LoopInterface $loop)
{
    $deferred = new Deferred();

    $timer = $loop->addTimer($time, function () use ($time, $promise, $deferred) {
        $deferred->reject(new TimeoutException($time, 'Timed out after ' . $time . ' seconds'));

        if ($promise instanceof CancellablePromiseInterface) {
            $promise->cancel();
        }
    });

    $promise->then(function ($v) use ($timer, $loop, $deferred) {
        $loop->cancelTimer($timer);
        $deferred->resolve($v);
    }, function ($v) use ($timer, $loop, $deferred) {
        $loop->cancelTimer($timer);
        $deferred->reject($v);
    });

    return $deferred->promise();
}

function resolve($time, LoopInterface $loop)
{
    $deferred = new Deferred();

    $timer = $loop->addTimer($time, function () use ($time, $deferred) {
        $deferred->resolve($time);
    });

    return $deferred->promise();
}

function reject($time, LoopInterface $loop)
{
    return resolve($time, $loop)->then(function ($time) {
        throw new TimeoutException($time, 'Timer expired after ' . $time . ' seconds');
    });
}
