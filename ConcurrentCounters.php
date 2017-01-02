<?php
/**
 * Create five concurrent counters counting up to 10000
 *
 * Each thread is independent so no need for locks or mutex
 */

const MAX = 10000;
const NTHREADS = 5;
const FIB = 10;

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
    public function decrement()
    {
        return --$this->value;
    }
    public function getCount()
    {
        return $this->value;
    }
}

class My extends Thread
{

    private $counters = array();
    private $id;

    /** all threads share the same counter dependency */
    public function __construct(Counter $counter, $id = null)
    {
        if (is_null($id) || !is_int($id) || $id < 0) {
            throw new InvalidArgumentException('id must be non-negative integrer');
        }
        $this->counters[$id] = $counter;
        $this->id = $id;
    }

    /** work will execute from job 1 to 1000, and no more, across all threads **/
    public function run()
    {
        $id = $this->id;
        echo "run id $id\n";
        $counter = $this->counters[$id];

        while (($job = $counter->increment()) < MAX) {
            //printf("Thread %d %lu doing job %d\n", Thread::getCurrentThreadId(), $id, $job);
            // burn some cpu!!
            Fibonacci::fib(FIB);
        }
        printf("Thread %lu id %d doing last job %d\n", Thread::getCurrentThreadId(), $id, $job);
    }

    public function getCounter()
    {
        return $this->counters[$this->id];
    }
    public function getId()
    {
        return $this->id;
    }
}

class Fibonacci
{
    public static function fib($num)
    {
        return ($num <= 2) ? $num : (self::fib($num-1) + self::fib($num-2));
    }
}

$threads = [];

// create and start threads
while (($tid = count($threads)) < NTHREADS) {
    $threads[$tid] = new My(new Counter, $tid+1);
    $threads[$tid]->start();
}

// wait for threads to finish
foreach ($threads as $thread) {
    $thread->join();
}

// display results from the counters
foreach ($threads as $thread) {
    echo "id ".$thread->getId().", count: ".$thread->getCounter()->getCount().PHP_EOL;
}
