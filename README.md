# pthreads_examples
The first section is mine. The rest is gleaned from SO. - Stu

--

Stu's musings...

Joe Watkins is the author of PHP pthreads. [Official documentation](http://php.net) is expected to be out-of-date. Joe is "one guy" and the source of the most accurate info.

Version: Use pthreads ver 3, preferably with PHP 7.

Pool and primitives: Learn how to use Pool, synchronize, wait, notify. This is probably all you need. Create classes that extend Threaded. Avoid Thread objects or use at your own risk.

Locks: Avoid locks. There are easier ways to do things.

Garbage: Know that collect is about garbage, not results.

Returning results: Know that variables in a Threaded can and probably will disappear before you get their value if you don't synchronize.

Returning arrays: To return an array from Threaded you must cast the array to an array in the body of synchronized!

Do this:

```code
$stuff = array('k1' => $v1, 'k2' => $v2);
$results = (array) $stuff;
```
instead of this:
```code
$stuff = array('k1' => $v1, 'k2' => $v2);
$results = $stuff;
```

See PoolExampleAllReady.php for example on returning array data.

KISS: Minimize locking and blocking. If you don't need it, don't use it. Not all concurrency needs locks or synchronization.

Look for examples here: <https://github.com/krakjoe/pthreads/tree/master/examples> but note that they are getting old and some may have problems.

Read Joe's authoritative answers on SO: <http://stackoverflow.com/users/1658631/joe-watkins>

Joe's answers often refute and correct common misunderstandings.

--

ref: <http://stackoverflow.com/questions/35091882/php-pthreads-locking-variable>

Calling synchronized ensures that no other context can enter a synchronized block for the same object while the calling context is, this ensures safety for the operations provided in the synchronized block.

Operations on objects members are atomic - methods can be assumed to be atomic, in other words.

It's too hard for you to guess which operations are going to be atomic, so don't.

Assumptions are horrible; as soon as the code in the synchronized block is more complex than single instructions your code is open to race conditions. The sensible thing to do is set a standard that says if you require atomicity for any number of statements they should be executed in a synchronized block.

[Joe Watkins](http://stackoverflow.com/users/1658631/joe-watkins)

--

ref: <http://stackoverflow.com/questions/14126696/php-when-to-use-pthread/14145979#14145979>

There are many examples included in the distribution and available on github:

<https://github.com/krakjoe/pthreads/tree/master/examples>

These examples include such things as a general purpose thread pool, a multi-threaded socket server and an SQLWorker.

The Threads pthreads creates are as sane, and as safe, as the threads that Zend itself sets up to service requests via a multi-threaded SAPI. They are compatible with all the same functionality, plus all you expect from a high level threading API (nearly).

There will always be limitations to implementing threading deep in the bowels of a shared nothing architecture, but the benefits, in terms of using better the physical resources at your disposal, but also the overall usability of PHP for any given task far outweigh the overhead of working around that environment.

The objects included in pthreads work as any other PHP object does, you can read, write and execute their methods, from any context with a reference to the object.

You are thinking exactly along the right lines: a measure of efficiency is not in the number of threads your application executes but how those threads are utilized to best serve the primary purpose of the application. Workers are a good idea, wherever you can use them, do so.

With regard to the specific things you asked about, a LoggingWorker is a good idea and will work, do not attempt to share that stream as there is no point, it will be perfectly stable if the Worker opens the log file, or database connection and stackables executed by it can access them. An SQLWorker is included in the examples, again, another good idea where the API lacks a decent async API, or you just get to prefer the flow of multi-threaded programming.

You won't get a better, or more correct answer: I wrote pthreads, on my own.

[Joe Watkins](http://stackoverflow.com/users/1658631/joe-watkins)

--
