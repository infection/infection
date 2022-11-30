<?php












class SyncMutex
{









public function __construct($name) {}











public function lock($wait = -1) {}











public function unlock($all = false) {}
}









class SyncSemaphore
{











public function __construct($name, $initialval = 1, $autounlock = true) {}











public function lock($wait = -1) {}











public function unlock(&$prevcount = 0) {}
}









class SyncEvent
{











public function __construct(string $name, bool $manual = false, bool $prefire = false) {}










public function fire() {}









public function reset() {}











public function wait($wait = -1) {}
}









class SyncReaderWriter
{










public function __construct($name, $autounlock = true) {}











public function readlock($wait = -1) {}










public function readunlock() {}











public function writelock($wait = -1) {}










public function writeunlock() {}
}











class SyncSharedMemory
{










public function __construct($name, $size) {}









public function first() {}












public function read($start = 0, $length) {}









public function size() {}











public function write($string, $start = 0) {}
}
