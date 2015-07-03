# react/promise-timer [![Build Status](https://travis-ci.org/reactphp/promise-timer.svg?branch=master)](https://travis-ci.org/reactphp/promise-timer)

A trivial implementation of timeouts for `Promise`s, built on top of [React PHP](http://reactphp.org/).

## Usage

This lightweight library consists only of a few simple functions.
All functions reside under the `React\Promise\Timer` namespace.

The below examples assume you use an import statement similar to this:

```php
use React\Promise\Timer;

Timer\timeout(…);
```

Alternatively, you can also refer to them with their fully-qualified name:

```php
\React\Promise\Timer\timeout(…);
``` 

### timeout()

The `timeout(PromiseInterface $promise, $time, LoopInterface $loop)` function
can be used to *cancel* operations that take *too long*.
You need to pass in an input `$promise` that represents a pending operation and timeout parameters.
It returns a new `Promise` with the following resolution behavior:

* If the input `$promise` resolves before `$time` seconds, resolve the resulting promise with its fulfillment value.
* If the input `$promise` rejects before `$time` seconds, reject the resulting promise with its rejection value.
* If the input `$promise` does not settle before `$time` seconds, *cancel* the operation and reject the resulting promise with a [`TimeoutException`](#timeoutexception).

A common use case for handling only resolved values looks like this:

```php
$promise = accessSomeRemoteResource();
Timer\timeout($promise, 10.0, $loop)->then(function ($value) {
    // the operation finished within 10.0 seconds
});
```

A more complete example could look like this:

```php
$promise = accessSomeRemoteResource();
Timer\timeout($promise, 10.0, $loop)->then(
    function ($value) {
        // the operation finished within 10.0 seconds
    },
    function ($error) {
        if ($error instanceof Timer\TimeoutException) {
            // the operation has failed due to a timeout
        } else {
            // the input operation has failed due to some other error
        }
    }
);
```

Or if you're using [react/promise v2.2.0](https://github.com/reactphp/promise) or up:

```php
Timer\timeout($promise, 10.0, $loop)
    ->then(function ($value) {
        // the operation finished within 10.0 seconds
    })
    ->otherwise(function (Timer\TimeoutException $error) {
        // the operation has failed due to a timeout
    })
    ->otherwise(function ($error) {
        // the input operation has failed due to some other error
    })
;
```

#### Timeout cancellation

As discussed above, the [`timeout()`](#timeout) function will *cancel* the
underlying operation if it takes *too long*.
This means that you can be sure the resulting promise will then be rejected
with a [`TimeoutException`](#timeoutexception).

However, what happens to the underlying input `$promise` is a bit more tricky:
Once the timer fires, we will try to call
[`$promise->cancel()`](https://github.com/reactphp/promise#cancellablepromiseinterfacecancel)
on the input `$promise` which in turn invokes its [cancellation handler](#cancellation-handler).

This means that it's actually up the input `$promise` to handle
[cancellation support](https://github.com/reactphp/promise#cancellablepromiseinterface).

* A common use case involves cleaning up any resources like open network sockets or
  file handles or terminating external processes or timers.

* If the given input `$promise` does not support cancellation, then this is a NO-OP.
  This means that while the resulting promise will still be rejected, the underlying
  input `$promise` may still be pending and can hence continue consuming resources.

See the following chapter for more details on the cancellation handler.

#### Cancellation handler

For example, an implementation for the above operation could look like this:

```php
function accessSomeRemoteResource()
{
    return new Promise(
        function ($resolve, $reject) use (&$socket) {
            // this will be called once the promise is created
            // a common use case involves opening any resources and eventually resolving
            $socket = createSocket();
            $socket->on('data', function ($data) use ($resolve) {
                $resolve($data);
            });
        },
        function ($resolve, $reject) use (&$socket) {
            // this will be called once calling `cancel()` on this promise
            // a common use case involves cleaning any resources and then rejecting
            $socket->close();
            $reject(new \RuntimeException('Operation cancelled'));
        }
    );
}
```

In this example, calling `$promise->cancel()` will invoke the registered cancellation
handler which then closes the network socket and rejects the `Promise` instance.

If no cancellation handler is passed to the `Promise` constructor, then invoking
its `cancel()` method it is effectively a NO-OP.
This means that it may still be pending and can hence continue consuming resources.

> Note: If you're stuck on legacy versions (PHP 5.3), then this is also a NO-OP,
as the Promise cancellation API is currently only available in
[react/promise v2.1.0](https://github.com/reactphp/promise)
which in turn requires PHP 5.4 or up.
It is assumed that if you're actually still stuck on PHP 5.3, resource cleanup
is likely one of your smaller problems. 

For more details on the promise cancellation, please refer to the
[Promise documentation](https://github.com/reactphp/promise#cancellablepromiseinterface).

#### Collections

If you want to wait for multiple promises to resolve, you can use the normal promise primitives like this:

```php
$promises = array(
    accessSomeRemoteResource(),
    accessSomeRemoteResource(),
    accessSomeRemoteResource()
);

$promise = \React\Promise\all($promises);

Timer\timeout($promise, 10, $loop)->then(function ($values) {
    // *all* promises resolved
});
```

The applies to all promise collection primitives alike, i.e. `all()`, `race()`, `any()`, `some()` etc.

For more details on the promise primitives, please refer to the
[Promise documentation](https://github.com/reactphp/promise#functions).

### resolve()

The `resolve($time, LoopInterface $loop)` function can be used to create a new Promise that
resolves in `$time` seconds with the `$time` as the fulfillment value.

```php
Timer\resolve(1.5, $loop)->then(function ($time) {
    echo 'Thanks for waiting ' . $time . ' seconds' . PHP_EOL;
});
```

#### Resolve cancellation

You can explicitly `cancel()` the resulting timer promise at any time:

```php
$timer = Timer\resolve(2.0, $loop);

$timer->cancel();
```

This will abort the timer and *reject* with a `RuntimeException`.

> Note: If you're stuck on legacy versions (PHP 5.3), then the `cancel()` method
is not available, as the Promise cancellation API is currently only available in
[react/promise v2.1.0](https://github.com/reactphp/promise)
which in turn requires PHP 5.4 or up.

### reject()

The `reject($time, LoopInterface $loop)` function can be used to create a new Promise
which rejects in `$time` seconds with a `TimeoutException`.

```php
Timer\reject(2.0, $loop)->then(null, function (TimeoutException $e) {
    echo 'Rejected after ' . $e->getTimeout() . ' seconds ' . PHP_EOL;
});
```

This function complements the [`resolve()`](#resolve) function
and can be used as a basic building block for higher-level promise consumers.

#### Reject cancellation

You can explicitly `cancel()` the resulting timer promise at any time:

```php
$timer = Timer\reject(2.0, $loop);

$timer->cancel();
```

This will abort the timer and *reject* with a `RuntimeException`.

> Note: If you're stuck on legacy versions (PHP 5.3), then the `cancel()` method
is not available, as the Promise cancellation API is currently only available in
[react/promise v2.1.0](https://github.com/reactphp/promise)
which in turn requires PHP 5.4 or up.

### TimeoutException

The `TimeoutException` extends PHP's built-in `RuntimeException`.

The `getTimeout()` method can be used to get the timeout value in seconds.

## Install

The recommended way to install this library is [through composer](http://getcomposer.org).
[New to composer?](http://getcomposer.org/doc/00-intro.md)

```JSON
{
    "require": {
        "react/promise-timer": "~1.0"
    }
}
```

> Note: If you're stuck on legacy versions (PHP 5.3), then the `cancel()` method
is not available,
as the Promise cancellation API is currently only available in
[react/promise v2.1.0](https://github.com/reactphp/promise)
which in turn requires PHP 5.4 or up.

## License

MIT
