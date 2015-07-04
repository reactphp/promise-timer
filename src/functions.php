<?php

namespace Clue\Promise\Timer;

use React\Promise\CancellablePromiseInterface;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use React\Promise\Promise;

function timeout(PromiseInterface $promise, $time, LoopInterface $loop)
{
    return new Promise(function ($resolve, $reject) use ($loop, $time, $promise) {
        $timer = $loop->addTimer($time, function () use ($time, $promise, $reject) {
            $reject(new TimeoutException($time, 'Timed out after ' . $time . ' seconds'));

            if ($promise instanceof CancellablePromiseInterface) {
                $promise->cancel();
            }
        });

        $promise->then(function ($v) use ($timer, $loop, $resolve) {
            $loop->cancelTimer($timer);
            $resolve($v);
        }, function ($v) use ($timer, $loop, $reject) {
            $loop->cancelTimer($timer);
            $reject($v);
        });
    });
}

function resolve($time, LoopInterface $loop)
{
    return new Promise(function ($resolve) use ($loop, $time) {
        $loop->addTimer($time, function () use ($time, $resolve) {
            $resolve($time);
        });
    });
}

function reject($time, LoopInterface $loop)
{
    return resolve($time, $loop)->then(function ($time) {
        throw new TimeoutException($time, 'Timer expired after ' . $time . ' seconds');
    });
}
