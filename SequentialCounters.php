<?php
/**
 * Create five sequential counters each counting up to 10000
 *
 * This is NON concurrent and just for runtime comparison.
 *
 */

const MAX = 10000;
const NCOUNTERS = 5;
const FIB = 10;

class Counter
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

class Fibonacci
{
    public static function fib($num)
    {
        return ($num <= 2) ? $num : (self::fib($num-1) + self::fib($num-2));
    }
}

$counters = [];

// create and run counters sequentially
$init = 0;
while (($tid = count($counters)) < NCOUNTERS) {
    $counters[$tid] = $counter = new Counter($init);
    while (($i = $counter->increment()) < ($init+MAX)) {
        // burn some cpu!!
        Fibonacci::fib(FIB);
    }
    $init = $counter->getCount();
}

// display results from the counters
foreach ($counters as $counter) {
    echo "count: ".$counter->getCount().PHP_EOL;
}
