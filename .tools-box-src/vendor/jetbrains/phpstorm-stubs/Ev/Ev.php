<?php

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;




final class Ev
{



public const FLAG_AUTO = 0;






public const FLAG_NOENV = 16777216;







public const FLAG_FORKCHECK = 33554432;






public const FLAG_NOINOTIFY = 1048576;







public const FLAG_SIGNALFD = 2097152;







public const FLAG_NOSIGMASK = 4194304;







public const RUN_NOWAIT = 1;







public const RUN_ONCE = 2;




public const BREAK_CANCEL = 0;




public const BREAK_ONE = 1;




public const BREAK_ALL = 2;




public const MINPRI = -2;




public const MAXPRI = 2;




public const READ = 1;




public const WRITE = 2;




public const TIMER = 256;




public const PERIODIC = 512;




public const SIGNAL = 1024;




public const CHILD = 2048;




public const STAT = 4096;




public const IDLE = 8192;





public const PREPARE = 16384;






public const CHECK = 32768;




public const EMBED = 65536;





public const CUSTOM = 16777216;






public const ERROR = -2147483648;




public const BACKEND_SELECT = 1;




public const BACKEND_POLL = 2;




public const BACKEND_EPOLL = 4;





public const BACKEND_KQUEUE = 8;




public const BACKEND_DEVPOLL = 16;




public const BACKEND_PORT = 32;






public const BACKEND_ALL = 255;





public const BACKEND_MASK = 65535;










final public static function backend() {}










final public static function depth() {}






final public static function embeddableBackends() {}












final public static function feedSignal(int $signum) {}










final public static function feedSignalEvent(int $signum) {}








final public static function iteration() {}










final public static function now() {}










final public static function nowUpdate() {}












final public static function recommendedBackends() {}














final public static function resume() {}











final public static function run(int $flags = self::FLAG_AUTO) {}






final public static function sleep(float $seconds) {}






final public static function stop(int $how = self::BREAK_ONE) {}






final public static function supportedBackends() {}












final public static function suspend() {}








final public static function time() {}







final public static function verify() {}
}




abstract class EvWatcher
{



#[Immutable]
public $is_active;






#[Immutable]
public $is_pending;




abstract public function __construct();




public $data;







public $priority;













public function clear() {}








public function feed(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $revents) {}






public function getLoop() {}






public function invoke(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $revents) {}















public function keepalive(#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $value = true) {}






public function setCallback(#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $callback) {}






public function start() {}






public function stop() {}
}






















final class EvCheck extends EvWatcher
{





public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $callback,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $data = null,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $priority = 0
) {}







final public static function createStopped(mixed $callback, mixed $data = null, int $priority = 0) {}
}












final class EvChild extends EvWatcher
{



#[Immutable]
public $pid;




#[Immutable]
public $rpid;




#[Immutable]
public $rstatus;


























public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $pid,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $trace,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $callback,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $data = null,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $priority = 0
) {}















final public static function createStopped(int $pid, bool $trace, mixed $callback, mixed $data = null, int $priority = 0) {}








public function set(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $pid,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $trace
) {}
}






final class EvEmbed extends EvWatcher
{



#[Immutable]
public $embed;


















public function __construct(
EvLoop $other,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $callback,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $data = null,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $priority = 0
) {}






public function set(EvLoop $other) {}




public function sweep() {}













final public static function createStopped(EvLoop $other, mixed $callback, mixed $data = null, int $priority = 0) {}
}























final class EvIo extends EvWatcher
{



#[Immutable]
public $fd;




#[Immutable]
#[ExpectedValues(flags: [Ev::READ, Ev::WRITE])]
public $events;












public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $fd,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $events,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $callback,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $data = null,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $priority = 0
) {}







public function set(
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $fd,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $events
) {}














final public static function createStopped(mixed $fd, int $events, mixed $callback, mixed $data = null, int $priority = 0) {}
}




















final class EvPeriodic extends EvWatcher
{





public $offset;





public $interval;



















public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'float'], default: '')] $offset,
#[LanguageLevelTypeAware(['8.0' => 'float'], default: '')] $interval,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $reschedule_cb,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $callback,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $data = null,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $priority = 0
) {}








public function again() {}










public function at() {}




















final public static function createStopped(float $offset, float $interval, mixed $reschedule_cb, mixed $callback, mixed $data = null, int $priority = 0) {}








public function set(
#[LanguageLevelTypeAware(['8.0' => 'float'], default: '')] $offset,
#[LanguageLevelTypeAware(['8.0' => 'float'], default: '')] $interval,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $reschedule_cb = null
) {}
}






















final class EvPrepare extends EvWatcher
{










public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $callback,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $data = null,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $priority = 0
) {}













final public static function createStopped(mixed $callback, mixed $data = null, int $priority = 0) {}
}
















final class EvSignal extends EvWatcher
{



#[Immutable]
public $signum;









public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $signum,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $callback,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $data = null,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $priority = 0
) {}






public function set(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $signum) {}














final public static function createStopped(int $signum, mixed $callback, mixed $data = null, int $priority = 0) {}
}























final class EvStat extends EvWatcher
{




#[Immutable]
public $interval;




#[Immutable]
public $path;













public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $path,
#[LanguageLevelTypeAware(['8.0' => 'float'], default: '')] $interval,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $callback,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $data = null,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $priority = 0
) {}




public function attr() {}




public function prev() {}








public function set(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $path,
#[LanguageLevelTypeAware(['8.0' => 'float'], default: '')] $interval
) {}









public function stat() {}















final public static function createStopped(string $path, float $interval, mixed $callback, mixed $data = null, int $priority = 0) {}
}





















final class EvTimer extends EvWatcher
{





public $repeat;










public $remaining;












public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'float'], default: '')] $after,
#[LanguageLevelTypeAware(['8.0' => 'float'], default: '')] $repeat,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $callback,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $data = null,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $priority = 0
) {}











public function again() {}









public function set(
#[LanguageLevelTypeAware(['8.0' => 'float'], default: '')] $after,
#[LanguageLevelTypeAware(['8.0' => 'float'], default: '')] $repeat
) {}














final public static function createStopped(float $after, float $repeat, mixed $callback, mixed $data = null, int $priority = 0) {}
}



















final class EvIdle extends EvWatcher
{







public function __construct(mixed $callback, mixed $data = null, int $priority = 0) {}










final public static function createStopped(mixed $callback, mixed $data = null, int $priority = 0) {}
}









final class EvFork extends EvWatcher
{







public function __construct(EvLoop $loop, mixed $callback, mixed $data = null, int $priority = 0) {}










final public static function createStopped(EvLoop $loop, mixed $callback, mixed $data = null, int $priority = 0) {}
}












final class EvLoop
{



#[Immutable]
#[ExpectedValues(flags: [Ev::BACKEND_ALL, Ev::BACKEND_DEVPOLL, Ev::BACKEND_EPOLL, Ev::BACKEND_KQUEUE, Ev::BACKEND_MASK, Ev::BACKEND_POLL, Ev::BACKEND_PORT, Ev::BACKEND_SELECT])]
public $backend;




#[Immutable]
public $is_default_loop;




public $data;




public $iteration;




public $pending;










public $io_interval;






public $timeout_interval;




public $depth;







public function __construct(int $flags = Ev::FLAG_AUTO, mixed $data = null, float $io_interval = 0.0, float $timeout_interval = 0.0) {}






public function backend() {}









final public function check(callable $callback, $data = null, $priority = 0) {}











final public function child(int $pid, bool $trace, mixed $callback, mixed $data = null, int $priority = 0) {}










final public function embed(EvLoop $other, callable $callback, $data = null, $priority = 0) {}









final public function fork(callable $callback, $data = null, $priority = 0) {}









final public function idle(mixed $callback, mixed $data = null, int $priority = 0) {}




public function invokePending() {}











final public function io(mixed $fd, int $events, mixed $callback, mixed $data = null, int $priority = 0) {}








public function loopFork() {}











public function now() {}










public function nowUpdate() {}











final public function periodic(float $offset, float $interval, mixed $reschedule_cb, mixed $callback, mixed $data = null, int $priority = 0) {}








final public function prepare(callable $callback, $data = null, $priority = 0) {}






public function resume() {}











public function run(int $flags = Ev::FLAG_AUTO) {}










final public function signal(int $signum, mixed $callback, mixed $data = null, int $priority = 0) {}











final public function stat(string $path, float $interval, mixed $callback, mixed $data = null, int $priority = 0) {}






public function stop(int $how = Ev::BREAK_ALL) {}






public function suspend() {}











final public function timer(float $after, float $repeat, mixed $callback, mixed $data = null, int $priority = 0) {}







public function verify() {}












public static function defaultLoop(
int $flags = Ev::FLAG_AUTO,
mixed $data = null,
float $io_interval = 0.0,
float $timeout_interval = 0.0
) {}
}
