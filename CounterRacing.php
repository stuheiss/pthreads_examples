<?php
/**
 * Create five concurrent instances of one counter, each counting up to 10000
 *
 * Threads are dependent and race conditions will give inconsistent results
 */

const MAX = 10000;
const NTHREADS = 5;

// note "extends Threaded"
class Counter extends Threaded
{
    private $value;

    public function __construct($value = 0)
    {
        $this->value = $value;
    }


    public function increment()
    {
        return ++$this->value;
    }

    public function getCount()
    {
        return $this->value;
    }
}

class CountingThread extends Thread
{
    private $counter = null;
    public function __construct($counter)
    {
        $this->counter = $counter;
    }
    public function run()
    {
        for ($x=0; $x<MAX; ++$x) {
            $this->counter->increment();
            //sleep(1);
        }
    }
    public function getCount()
    {
        return $this->counter->getCount();
    }
}

echo "#count up to " . MAX . PHP_EOL;
echo "#simple counter\n";
$counter = new Counter();
for ($x=0; $x<MAX; ++$x) {
    $counter->increment();
}
echo $counter->getCount() . PHP_EOL;

echo "#threaded counter\n";
$counter = new Counter();
$threads = [];

// create and start threads
while (($tid = count($threads)) < NTHREADS) {
    $threads[$tid] = new CountingThread($counter);
    $threads[$tid]->start();
}
// wait for threads to finish
foreach ($threads as $thread) {
    $thread->join();
}

echo count($threads) . " threads, " . $counter->getCount() . PHP_EOL;
