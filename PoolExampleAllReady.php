<?php
// ref: http://stackoverflow.com/questions/34857779/why-not-all-threads-are-completed
//
// Worker::collect is not intended to enable you to reap results; It is non-deterministic.
//
// Worker::collect is only intended to run garbage collection on objects referenced in the stack of Worker objects.
//
// wait for all results to become available, and possibly avoid some contention for locks
//
// fib class added to add delay to computation
// demo how to return array results

const CPUCORES = 4; // i5, i7 (macbook pro)
const NUMTASKS = 100;
const SILENT = false;
const MAXFIB = 25;

// 2 pools per core is about as good as it gets; IO bound may benefit from more
$pool = new Pool(2 * CPUCORES);
$results = new Volatile();
$expected = NUMTASKS;

class Fibonacci
{
    public static function fib($n)
    {
        return $n <= 2 ? $n : self::fib($n - 1) + self::fib($n - 2);
    }
}

while (@$i++ < $expected) {
    $pool->submit(new class($i, $results) extends Threaded {

        public function __construct($id, Volatile $results)
        {
            $this->id = $id;
            $this->results = $results;
        }

        public function run()
        {
            // return google search as string
            //$result = file_get_contents('http://google.fr?q=' . $this->id);

            // return plain string
            //$result = sprintf("Thread #%lu, id=%s", Thread::getCurrentThreadId(), $this->id);

            // return fib as string
            $num = $this->id % MAXFIB;
            //$result = sprintf("Thread #%lu, id=%s, fib(%d)=%d", Thread::getCurrentThreadId(), $this->id, $num, Fibonacci::fib($num));

            // return fib as array
            $result = array(
                'tid' => Thread::getCurrentThreadId(),
                'id'  => $this->id,
                'num' => $num,
                'fib' => Fibonacci::fib($num)
            );

            $this->results->synchronized(function ($results, $result) {
                if (is_array($result)) {
                    // note cast to array!
                    // this prevents "Fatal error: Uncaught RuntimeException: pthreads detected an attempt to connect to an object which has already been destroyed"
                    $results[$this->id] = (array) $result;
                } else {
                    // assume string
                    $results[$this->id] = $result;
                }
                $results->notify();
            }, $this->results, $result);
        }

        private $id;
        private $results;
    });
}

// wait for all the results to become available
$results->synchronized(function () use ($expected, $results) {
    while (count($results) != $expected) {
        $results->wait();
    }
});

if(!SILENT) echo "# got all " . count($results) . " results\n";
foreach ($results as $next) {
    if (is_array($next)) {
        //if (!SILENT) echo "# got next: " . print_r($next, true);
        $ary = array();
        foreach ($next as $k => $v) {
            $ary[] = "$k: $v";
        }
        if (!SILENT) echo "# got next: " . implode(', ', $ary) . PHP_EOL;
    } else {
        if (!SILENT) echo "# got next: $next\n";
    }
}

// garbage collect
while ($pool->collect()) continue;

$pool->shutdown();
