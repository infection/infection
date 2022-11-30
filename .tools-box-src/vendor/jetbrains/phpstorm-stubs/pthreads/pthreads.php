<?php








define('PTHREADS_INHERIT_ALL', 1118481);





define('PTHREADS_INHERIT_NONE', 0);





define('PTHREADS_INHERIT_INI', 1);





define('PTHREADS_INHERIT_CONSTANTS', 16);





define('PTHREADS_INHERIT_CLASSES', 4096);





define('PTHREADS_INHERIT_FUNCTIONS', 256);





define('PTHREADS_INHERIT_INCLUDES', 65536);





define('PTHREADS_INHERIT_COMMENTS', 1048576);





define('PTHREADS_ALLOW_HEADERS', 268435456);









class Pool
{




protected $size;





protected $class;





protected $ctor;





protected $workers;





protected $last;












public function __construct(int $size, string $class = 'Worker', array $ctor = []) {}











public function collect(?callable $collector = null) {}








public function resize(int $size) {}








public function shutdown() {}








public function submit(Threaded $task) {}











public function submitTo(int $worker, Threaded $task) {}
}










class Threaded implements Collectable, Traversable, Countable, ArrayAccess
{




protected $worker;






public function addRef() {}










public function chunk($size, $preserve = false) {}







public function count() {}






public function delRef() {}








public static function extend($class) {}






public function getRefCount() {}







public function isRunning() {}






public function isGarbage(): bool {}








public function isTerminated() {}









public function merge($from, $overwrite = true) {}







public function notify() {}









public function notifyOne() {}







public function pop() {}








public function run() {}







public function shift() {}











public function synchronized(Closure $block, ...$_) {}









public function wait(int $timeout = 0) {}





public function offsetExists($offset) {}





public function offsetGet($offset) {}





public function offsetSet($offset, $value) {}





public function offsetUnset($offset) {}
}










class Thread extends Threaded implements Countable, Traversable, ArrayAccess
{






public function getCreatorId() {}







public static function getCurrentThread() {}







public static function getCurrentThreadId() {}







public function getThreadId() {}







public function isJoined() {}







public function isStarted() {}







public function join() {}









public function start(int $options = PTHREADS_INHERIT_ALL) {}
}















class Worker extends Thread implements Traversable, Countable, ArrayAccess
{











public function collect(?callable $collector = null) {}








public function getStacked() {}







public function isShutdown() {}







public function shutdown() {}








public function stack(Threaded $work) {}







public function unstack() {}
}






interface Collectable
{






public function isGarbage(): bool;
}










class Volatile extends Threaded implements Collectable, Traversable {}
