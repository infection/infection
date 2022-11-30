<?php









class ZMQ
{



public const SOCKET_PAIR = 0;




public const SOCKET_PUB = 1;




public const SOCKET_SUB = 2;




public const SOCKET_REQ = 3;




public const SOCKET_REP = 4;




public const SOCKET_XREQ = 5;




public const SOCKET_XREP = 6;




public const SOCKET_PUSH = 8;




public const SOCKET_PULL = 7;




public const SOCKET_ROUTER = 6;




public const SOCKET_DEALER = 5;





public const SOCKET_XPUB = 9;




public const SOCKET_XSUB = 10;





public const SOCKET_STREAM = 11;








public const SOCKOPT_HWM = 1;





public const SOCKOPT_SNDHWM = 23;





public const SOCKOPT_RCVHWM = 24;




public const SOCKOPT_AFFINITY = 4;




public const SOCKOPT_IDENTITY = 5;




public const SOCKOPT_SUBSCRIBE = 6;




public const SOCKOPT_UNSUBSCRIBE = 7;




public const SOCKOPT_RATE = 8;




public const SOCKOPT_RECOVERY_IVL = 9;




public const SOCKOPT_RECONNECT_IVL = 18;




public const SOCKOPT_RECONNECT_IVL_MAX = 21;




public const SOCKOPT_MCAST_LOOP = 10;




public const SOCKOPT_SNDBUF = 11;




public const SOCKOPT_RCVBUF = 12;




public const SOCKOPT_RCVMORE = 13;




public const SOCKOPT_TYPE = 16;





public const SOCKOPT_LINGER = 17;





public const SOCKOPT_BACKLOG = 19;





public const SOCKOPT_MAXMSGSIZE = 22;





public const SOCKOPT_SNDTIMEO = 28;





public const SOCKOPT_RCVTIMEO = 27;





public const SOCKOPT_IPV4ONLY = 31;





public const SOCKOPT_LAST_ENDPOINT = 32;





public const SOCKOPT_TCP_KEEPALIVE_IDLE = 36;





public const SOCKOPT_TCP_KEEPALIVE_CNT = 35;





public const SOCKOPT_TCP_KEEPALIVE_INTVL = 37;





public const SOCKOPT_DELAY_ATTACH_ON_CONNECT = 39;





public const SOCKOPT_TCP_ACCEPT_FILTER = 38;





public const SOCKOPT_XPUB_VERBOSE = 40;






public const SOCKOPT_ROUTER_RAW = 41;





public const SOCKOPT_IPV6 = 42;





public const CTXOPT_MAX_SOCKETS = 2;




public const POLL_IN = 1;




public const POLL_OUT = 2;





public const MODE_NOBLOCK = 1;




public const MODE_DONTWAIT = 1;




public const MODE_SNDMORE = 2;




public const DEVICE_FORWARDER = 2;




public const DEVICE_QUEUE = 3;




public const DEVICE_STREAMER = 1;




public const ERR_INTERNAL = -99;




public const ERR_EAGAIN = 11;




public const ERR_ENOTSUP = 156384713;




public const ERR_EFSM = 156384763;




public const ERR_ETERM = 156384765;





private function __construct() {}
}




class ZMQContext
{









public function __construct($io_threads = 1, $is_persistent = true) {}











public function getOpt($key) {}
















public function getSocket($type, $persistent_id = null, $on_new_socket = null) {}










public function isPersistent() {}













public function setOpt($key, $value) {}
}




class ZMQSocket
{

















public function __construct(ZMQContext $context, $type, $persistent_id = null, $on_new_socket = null) {}















public function bind($dsn, $force = false) {}















public function connect($dsn, $force = false) {}














public function disconnect($dsn) {}









public function getEndpoints() {}










public function getPersistentId() {}
















public function getSockOpt($key) {}












public function getSocketType() {}








public function isPersistent() {}
















public function recv($mode = 0) {}














public function recvMulti($mode = 0) {}













public function send($message, $mode = 0) {}













public function sendmulti(array $message, $mode = 0) {}













public function setSockOpt($key, $value) {}













public function unbind($dsn) {}
}




class ZMQPoll
{














public function add(ZMQSocket $entry, $type) {}









public function clear() {}









public function count() {}











public function getLastErrors() {}

















public function poll(array &$readable, array &$writable, $timeout = -1) {}












public function remove($item) {}
}




class ZMQDevice
{













public function __construct(ZMQSocket $frontend, ZMQSocket $backend, ZMQSocket $listener = null) {}










public function getIdleTimeout() {}









public function getTimerTimeout() {}










public function run() {}















public function setIdleCallback($cb_func, $timeout, $user_data) {}











public function setIdleTimeout($timeout) {}















public function setTimerCallback($cb_func, $timeout, $user_data) {}











public function setTimerTimeout($timeout) {}
}
class ZMQException extends Exception {}
class ZMQContextException extends ZMQException {}
class ZMQSocketException extends ZMQException {}
class ZMQPollException extends ZMQException {}
class ZMQDeviceException extends ZMQException {}
