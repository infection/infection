<?php


use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;











final class Event
{



#[Immutable]
public $pending;
public const ET = 32;
public const PERSIST = 16;
public const READ = 2;
public const WRITE = 4;
public const SIGNAL = 8;
public const TIMEOUT = 1;













#[Pure]
public function __construct(EventBase $base, $fd, int $what, callable $cb, $arg = null) {}











public function add(float $timeout = -1): bool {}











public function addSignal(float $timeout = -1): bool {}











public function addTimer(float $timeout = -1): bool {}









public function del(): bool {}









public function delSignal(): bool {}









public function delTimer(): bool {}







public function free(): void {}









public static function getSupportedMethods(): array {}











public function pending(int $flags): bool {}















public function set(EventBase $base, $fd, int $what, callable $cb, $arg): bool {}










public function setPriority(int $priority): bool {}













public function setTimer(EventBase $base, callable $cb, $arg): bool {}














public static function signal(EventBase $base, int $signum, callable $cb, $arg): Event {}













public static function timer(EventBase $base, callable $cb, $arg): Event {}
}













final class EventBase
{
public const LOOP_ONCE = 1;
public const LOOP_NONBLOCK = 2;
public const NOLOCK = 1;
public const STARTUP_IOCP = 4;
public const NO_CACHE_TIME = 8;
public const EPOLL_USE_CHANGELIST = 16;
public const IGNORE_ENV = 2;
public const PRECISE_TIMER = 32;









public function __construct(?EventConfig $cfg = null) {}







public function dispatch(): void {}











public function exit(float $timeout = 0.0): bool {}







public function free(): void {}









#[Pure]
public function getFeatures(): int {}









#[Pure]
public function getMethod(): string {}









#[Pure]
public function getTimeOfDayCached(): float {}









#[Pure]
public function gotExit(): bool {}









#[Pure]
public function gotStop(): bool {}











public function loop(int $flags = -1): bool {}











public function priorityInit(int $n_priorities): bool {}









public function reInit(): bool {}







public function resume(): bool {}









public function stop(): bool {}







public function updateCacheTime(): bool {}
}












class EventBuffer
{



#[Immutable]
public $length;




#[Immutable]
public $contiguous_space;
public const EOL_ANY = 0;
public const EOL_CRLF = 1;
public const EOL_CRLF_STRICT = 2;
public const EOL_LF = 3;
public const EOL_NUL = 4;
public const PTR_SET = 0;
public const PTR_ADD = 1;







#[Pure]
public function __construct() {}











public function add(string $data): bool {}











public function addBuffer(EventBuffer $buf): bool {}












public function appendFrom(EventBuffer $buf, int $len): int {}












public function copyout(string &$data, int $max_bytes): int {}











public function drain(int $len): bool {}






public function enableLocking(): void {}











public function expand(int $len): bool {}











public function freeze(bool $at_front): bool {}







public function lock(): void {}











public function prepend(string $data): bool {}











public function prependBuffer(EventBuffer $buf): bool {}











public function pullup(int $size): ?string {}









public function read(int $max_bytes): ?string {}












public function readFrom($fd, int $howmuch): int {}











public function readLine(int $eol_style): ?string {}













public function search(string $what, int $start = 1, int $end = 1): int|false {}












public function searchEol(int $start = 1, int $eol_style = EventBuffer::EOL_ANY): int|false {}












public function substr(int $start, int $length): string {}











public function unfreeze(bool $at_front): bool {}









public function unlock(): void {}












public function write(mixed $fd, int $howmuch): int|false {}
}

















final class EventBufferEvent
{

public $fd;


public $priority;




#[Immutable]
public $input;




#[Immutable]
public $output;
public const READING = 1;
public const WRITING = 2;
public const EOF = 16;
public const ERROR = 32;
public const TIMEOUT = 64;
public const CONNECTED = 128;
public const OPT_CLOSE_ON_FREE = 1;
public const OPT_THREADSAFE = 2;
public const OPT_DEFER_CALLBACKS = 4;
public const OPT_UNLOCK_CALLBACKS = 8;
public const SSL_OPEN = 0;
public const SSL_CONNECTING = 1;
public const SSL_ACCEPTING = 2;














#[Pure]
public function __construct(EventBase $base, $socket = null, int $options = 0, ?callable $readcb = null, ?callable $writecb = null, ?callable $eventcb = null) {}







public function close(): bool {}











public function connect(string $addr): bool {}














public function connectHost(?EventDnsBase $dns_base, string $hostname, int $port, int $family = EventUtil::AF_UNSPEC): bool {}











public function createSslFilter(EventBufferEvent $underlying, EventSslContext $ctx, int $state, int $options = 0): EventBufferEvent {}












public static function createPair(EventBase $base, int $options = 0): array {}











public function disable(int $events): bool {}











public function enable(int $events): bool {}







public function free(): void {}









#[Pure]
public function getDnsErrorString(): string {}









#[Pure]
public function getEnabled(): int {}









#[Pure]
public function getInput(): EventBuffer {}









#[Pure]
public function getOutput(): EventBuffer {}











public function read(int $size): ?string {}











public function readBuffer(EventBuffer $buf): bool {}












public function setCallbacks(callable $readcb, callable $writecb, callable $eventcb, mixed $arg = null): void {}











public function setPriority(int $priority): bool {}












public function setTimeouts(float $timeout_read, float $timeout_write): bool {}











public function setWatermark(int $events, int $lowmark, int $highmark): void {}









public function sslError(): false|string {}















public static function sslFilter(EventBase $base, EventBufferEvent $underlying, EventSslContext $ctx, int $state, int $options = 0): EventBufferEvent {}









public function sslGetCipherInfo(): string|false {}









public function sslGetCipherName(): string|false {}









public function sslGetCipherVersion(): string|false {}









public function sslGetProtocol(): string {}







public function sslRenegotiate(): void {}















public static function sslSocket(EventBase $base, mixed $socket, EventSslContext $ctx, int $state, int $options = 0): EventBufferEvent {}











public function write(string $data): bool {}











public function writeBuffer(EventBuffer $buf): bool {}
}











final class EventConfig
{
public const FEATURE_ET = 1;
public const FEATURE_O1 = 2;
public const FEATURE_FDS = 4;







#[Pure]
public function __construct() {}











public function avoidMethod(string $method): bool {}











public function requireFeatures(int $feature): bool {}








public function setFlags(int $flags): bool {}











public function setMaxDispatchInterval(int $max_interval, int $max_callbacks, int $min_priority): void {}
}











final class EventDnsBase
{
public const OPTION_SEARCH = 1;
public const OPTION_NAMESERVERS = 2;
public const OPTION_MISC = 4;
public const OPTION_HOSTSFILE = 8;
public const OPTIONS_ALL = 15;










#[Pure]
public function __construct(EventBase $base, bool $initialize) {}











public function addNameserverIp(string $ip): bool {}









public function addSearch(string $domain): void {}







public function clearSearch(): void {}









public function countNameservers(): int {}











public function loadHosts(string $hosts): bool {}












public function parseResolvConf(int $flags, string $filename): bool {}












public function setOption(string $option, string $value): bool {}











public function setSearchNdots(int $ndots): void {}
}











final class EventHttp
{









public function __construct(EventBase $base, ?EventSslContext $ctx = null) {}











public function accept(mixed $socket): bool {}











public function addServerAlias(string $alias): bool {}











public function bind(string $address, int $port): bool {}











public function removeServerAlias(string $alias): bool {}









public function setAllowedMethods(int $methods): void {}











public function setCallback(string $path, string $cb, ?string $arg = null): bool {}










public function setDefaultCallback(string $cb, ?string $arg = null): void {}









public function setMaxBodySize(int $value): void {}









public function setMaxHeadersSize(int $value): void {}









public function setTimeout(int $value): void {}
}











class EventHttpConnection
{












#[Pure]
public function __construct(EventBase $base, EventDnsBase $dns_base, string $address, int $port, ?EventSslContext $ctx = null) {}









public function getBase(): false|EventBase {}










public function getPeer(string &$address, int &$port): void {}













public function makeRequest(EventHttpRequest $req, int $type, string $uri): bool {}










public function setCloseCallback(callable $callback, mixed $data = null): void {}









public function setLocalAddress(string $address): void {}









public function setLocalPort(int $port): void {}









public function setMaxBodySize(string $max_size): void {}









public function setMaxHeadersSize(string $max_size): void {}









public function setRetries(int $retries): void {}









public function setTimeout(int $timeout): void {}
}


class EventHttpRequest
{
public const CMD_GET = 1;
public const CMD_POST = 2;
public const CMD_HEAD = 4;
public const CMD_PUT = 8;
public const CMD_DELETE = 16;
public const CMD_OPTIONS = 32;
public const CMD_TRACE = 64;
public const CMD_CONNECT = 128;
public const CMD_PATCH = 256;
public const INPUT_HEADER = 1;
public const OUTPUT_HEADER = 2;






#[Pure]
public function __construct(
callable $callback,
$data = null
) {}

public function addHeader(string $key, string $value, int $type): bool {}

public function cancel(): void {}

public function clearHeaders(): void {}

public function closeConnection(): void {}

public function findHeader(string $key, string $type): ?string {}

public function free() {}

#[Pure]
public function getCommand(): int {}

#[Pure]
public function getConnection(): ?EventHttpConnection {}

#[Pure]
public function getHost(): string {}

#[Pure]
public function getInputBuffer(): EventBuffer {}

#[Pure]
public function getInputHeaders(): array {}

#[Pure]
public function getOutputBuffer(): EventBuffer {}

#[Pure]
public function getOutputHeaders(): array {}

#[Pure]
public function getResponseCode(): int {}

#[Pure]
public function getUri(): string {}

public function removeHeader(string $key, int $type): bool {}

public function sendError(int $error, ?string $reason = null) {}

public function sendReply(int $code, string $reason, ?EventBuffer $buf = null) {}

public function sendReplyChunk(EventBuffer $buf) {}

public function sendReplyEnd(): void {}

public function sendReplyStart(int $code, string $reason): void {}
}











final class EventListener
{



#[Immutable]
public $fd;
public const OPT_LEAVE_SOCKETS_BLOCKING = 1;
public const OPT_CLOSE_ON_FREE = 2;
public const OPT_CLOSE_ON_EXEC = 4;
public const OPT_REUSEABLE = 8;
public const OPT_THREADSAFE = 16;
public const OPT_DISABLED = 32;
public const OPT_DEFERRED_ACCEPT = 64;














public function __construct(EventBase $base, callable $cb, mixed $data, int $flags, int $backlog, mixed $target) {}









public function disable(): bool {}









public function enable(): bool {}

public function free(): void {}







public function getBase(): void {}












public static function getSocketName(string &$address, int &$port): bool {}










public function setCallback(callable $cb, mixed $arg = null): void {}









public function setErrorCallback(string $cb): void {}
}












final class EventSslContext
{
public const SSLv2_CLIENT_METHOD = 1;
public const SSLv3_CLIENT_METHOD = 2;
public const SSLv23_CLIENT_METHOD = 3;
public const TLS_CLIENT_METHOD = 4;
public const SSLv2_SERVER_METHOD = 5;
public const SSLv3_SERVER_METHOD = 6;
public const SSLv23_SERVER_METHOD = 7;
public const TLS_SERVER_METHOD = 8;
public const TLSv11_CLIENT_METHOD = 9;
public const TLSv11_SERVER_METHOD = 10;
public const TLSv12_CLIENT_METHOD = 11;
public const TLSv12_SERVER_METHOD = 12;
public const OPT_LOCAL_CERT = 1;
public const OPT_LOCAL_PK = 2;
public const OPT_PASSPHRASE = 3;
public const OPT_CA_FILE = 4;
public const OPT_CA_PATH = 5;
public const OPT_ALLOW_SELF_SIGNED = 6;
public const OPT_VERIFY_PEER = 7;
public const OPT_VERIFY_DEPTH = 8;
public const OPT_CIPHERS = 9;
public const OPT_NO_SSLv2 = 10;
public const OPT_NO_SSLv3 = 11;
public const OPT_NO_TLSv1 = 12;
public const OPT_NO_TLSv1_1 = 13;
public const OPT_NO_TLSv1_2 = 14;
public const OPT_CIPHER_SERVER_PREFERENCE = 15;
public const OPT_REQUIRE_CLIENT_CERT = 16;
public const OPT_VERIFY_CLIENT_ONCE = 17;




public $local_cert;




public $local_pk;










#[Pure]
public function __construct(int $method, array $options) {}






public function setMinProtoVersion(int $proto): bool {}






public function setMaxProtoVersion(int $proto): bool {}
}











final class EventUtil
{
public const AF_INET = 2;
public const AF_INET6 = 10;
public const AF_UNIX = 1;
public const AF_UNSPEC = 0;
public const LIBEVENT_VERSION_NUMBER = 33559808;
public const SO_DEBUG = 1;
public const SO_REUSEADDR = 2;
public const SO_KEEPALIVE = 9;
public const SO_DONTROUTE = 5;
public const SO_LINGER = 13;
public const SO_BROADCAST = 6;
public const SO_OOBINLINE = 10;
public const SO_SNDBUF = 7;
public const SO_RCVBUF = 8;
public const SO_SNDLOWAT = 19;
public const SO_RCVLOWAT = 18;
public const SO_SNDTIMEO = 21;
public const SO_RCVTIMEO = 20;
public const SO_TYPE = 3;
public const SO_ERROR = 4;
public const SOL_SOCKET = 1;
public const SOL_TCP = 6;
public const SOL_UDP = 17;
public const SOCK_RAW = 3;
public const TCP_NODELAY = 1;
public const IPPROTO_IP = 0;
public const IPPROTO_IPV6 = 41;







abstract public function __construct();





public function createSocket(mixed $socket) {}











public static function getLastSocketErrno($socket = null): int|false {}











public static function getLastSocketError(mixed $socket): string|false {}











public static function getSocketFd(mixed $socket): int {}













public static function getSocketName(mixed $socket, string &$address, int &$port): bool {}














public static function setSocketOption(mixed $socket, int $level, int $optname, int|array $optval): bool {}







public static function sslRandPoll(): bool {}
}
