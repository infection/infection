<?php


use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;

















function socket_addrinfo_lookup(string $host, ?string $service, array $hints = []): array|false {}











function socket_addrinfo_connect(AddressInfo $address): Socket|false {}












function socket_addrinfo_bind(AddressInfo $address): Socket|false {}











function socket_addrinfo_explain(AddressInfo $address): array {}

















































function socket_select(?array &$read, ?array &$write, ?array &$except, ?int $seconds, int $microseconds = 0): int|false {}









































































































































function socket_create(int $domain, int $type, int $protocol): Socket|false {}





function socket_export_stream(Socket $socket) {}




















function socket_create_listen(int $port, int $backlog = 128): Socket|false {}
































function socket_create_pair(int $domain, int $type, int $protocol, &$pair): bool {}













function socket_accept(Socket $socket): Socket|false {}










function socket_set_nonblock(Socket $socket): bool {}










function socket_set_block(Socket $socket): bool {}




























function socket_listen(Socket $socket, int $backlog = 0): bool {}










function socket_close(Socket $socket): void {}


























function socket_write(Socket $socket, string $data, ?int $length = null): int|false {}






























function socket_read(Socket $socket, int $length, int $mode = PHP_BINARY_READ): string|false {}






























function socket_getsockname(Socket $socket, &$address, &$port = null): bool {}
































function socket_getpeername(Socket $socket, &$address, &$port = null): bool {}





























function socket_connect(Socket $socket, string $address, ?int $port = null): bool {}











function socket_strerror(int $error_code): string {}





























function socket_bind(Socket $socket, string $address, int $port = 0): bool {}































































function socket_recv(Socket $socket, &$data, int $length, int $flags): int|false {}



















































function socket_send(Socket $socket, string $data, int $length, int $flags): int|false {}











function socket_sendmsg(
Socket $socket,
array $message,
#[PhpStormStubsElementAvailable(from: '5.5', to: '7.4')] int $flags,
#[PhpStormStubsElementAvailable(from: '8.0')] int $flags = 0
): int|false {}









































































function socket_recvfrom(Socket $socket, &$data, int $length, int $flags, &$address, &$port = null): int|false {}










function socket_recvmsg(
Socket $socket,
array &$message,
#[PhpStormStubsElementAvailable(from: '5.5', to: '7.4')] int $flags,
#[PhpStormStubsElementAvailable(from: '8.0')] int $flags = 0
): int|false {}


























































function socket_sendto(Socket $socket, string $data, int $length, int $flags, string $address, ?int $port = null): int|false {}













































































































































































































































































































































































function socket_get_option(Socket $socket, int $level, int $option): array|int|false {}


























function socket_set_option(Socket $socket, int $level, int $option, $value): bool {}

































function socket_shutdown(Socket $socket, int $mode = 2): bool {}









function socket_last_error(?Socket $socket = null): int {}









function socket_clear_error(?Socket $socket = null): void {}










function socket_import_stream($stream): Socket|false {}










function socket_cmsg_space(
int $level,
int $type,
#[PhpStormStubsElementAvailable(from: '8.0')] int $num = 0
): ?int {}







function socket_getopt(Socket $socket, int $level, int $option): array|int|false {}









function socket_setopt(Socket $socket, int $level, int $option, $value): bool {}












function socket_wsaprotocol_info_export($socket, $target_pid) {}











function socket_wsaprotocol_info_import($info_id) {}











function socket_wsaprotocol_info_release($info_id) {}

define('AF_UNIX', 1);
define('AF_INET', 2);





define('AF_INET6', 10);
define('SOCK_STREAM', 1);
define('SOCK_DGRAM', 2);
define('SOCK_RAW', 3);
define('SOCK_SEQPACKET', 5);
define('SOCK_RDM', 4);
define('MSG_OOB', 1);
define('MSG_WAITALL', 256);
define('MSG_CTRUNC', 8);
define('MSG_TRUNC', 32);
define('MSG_PEEK', 2);
define('MSG_DONTROUTE', 4);





define('MSG_EOR', 128);





define('MSG_EOF', 512);
define('MSG_CONFIRM', 2048);
define('MSG_ERRQUEUE', 8192);
define('MSG_NOSIGNAL', 16384);
define('MSG_DONTWAIT', 64);
define('MSG_MORE', 32768);
define('MSG_WAITFORONE', 65536);
define('MSG_CMSG_CLOEXEC', 1073741824);
define('SO_DEBUG', 1);
define('SO_REUSEADDR', 2);







define('SO_REUSEPORT', 15);
define('SO_KEEPALIVE', 9);
define('SO_DONTROUTE', 5);
define('SO_LINGER', 13);
define('SO_BROADCAST', 6);
define('SO_OOBINLINE', 10);
define('SO_SNDBUF', 7);
define('SO_RCVBUF', 8);
define('SO_SNDLOWAT', 19);
define('SO_RCVLOWAT', 18);
define('SO_SNDTIMEO', 21);
define('SO_RCVTIMEO', 20);
define('SO_TYPE', 3);
define('SO_ERROR', 4);
define('SO_BINDTODEVICE', 25);
define('SOL_SOCKET', 1);
define('SOMAXCONN', 128);



define('SO_MARK', 36);





define('TCP_NODELAY', 1);
define('PHP_NORMAL_READ', 1);
define('PHP_BINARY_READ', 2);





define('MCAST_JOIN_GROUP', 42);





define('MCAST_LEAVE_GROUP', 45);






define('MCAST_BLOCK_SOURCE', 43);







define('MCAST_UNBLOCK_SOURCE', 44);






define('MCAST_JOIN_SOURCE_GROUP', 46);






define('MCAST_LEAVE_SOURCE_GROUP', 47);





define('IP_MULTICAST_IF', 32);





define('IP_MULTICAST_TTL', 33);








define('IP_MULTICAST_LOOP', 34);





define('IPV6_MULTICAST_IF', 17);







define('IPV6_MULTICAST_HOPS', 18);






define('IPV6_MULTICAST_LOOP', 19);
define('IPV6_V6ONLY', 26);





define('SOCKET_EPERM', 1);





define('SOCKET_ENOENT', 2);





define('SOCKET_EINTR', 4);





define('SOCKET_EIO', 5);





define('SOCKET_ENXIO', 6);





define('SOCKET_E2BIG', 7);





define('SOCKET_EBADF', 9);





define('SOCKET_EAGAIN', 11);





define('SOCKET_ENOMEM', 12);





define('SOCKET_EACCES', 13);





define('SOCKET_EFAULT', 14);





define('SOCKET_ENOTBLK', 15);





define('SOCKET_EBUSY', 16);





define('SOCKET_EEXIST', 17);





define('SOCKET_EXDEV', 18);





define('SOCKET_ENODEV', 19);





define('SOCKET_ENOTDIR', 20);





define('SOCKET_EISDIR', 21);





define('SOCKET_EINVAL', 22);





define('SOCKET_ENFILE', 23);





define('SOCKET_EMFILE', 24);





define('SOCKET_ENOTTY', 25);





define('SOCKET_ENOSPC', 28);





define('SOCKET_ESPIPE', 29);





define('SOCKET_EROFS', 30);





define('SOCKET_EMLINK', 31);





define('SOCKET_EPIPE', 32);





define('SOCKET_ENAMETOOLONG', 36);





define('SOCKET_ENOLCK', 37);





define('SOCKET_ENOSYS', 38);





define('SOCKET_ENOTEMPTY', 39);





define('SOCKET_ELOOP', 40);





define('SOCKET_EWOULDBLOCK', 11);





define('SOCKET_ENOMSG', 42);





define('SOCKET_EIDRM', 43);





define('SOCKET_ECHRNG', 44);





define('SOCKET_EL2NSYNC', 45);





define('SOCKET_EL3HLT', 46);





define('SOCKET_EL3RST', 47);





define('SOCKET_ELNRNG', 48);





define('SOCKET_EUNATCH', 49);





define('SOCKET_ENOCSI', 50);





define('SOCKET_EL2HLT', 51);





define('SOCKET_EBADE', 52);





define('SOCKET_EBADR', 53);





define('SOCKET_EXFULL', 54);





define('SOCKET_ENOANO', 55);





define('SOCKET_EBADRQC', 56);





define('SOCKET_EBADSLT', 57);





define('SOCKET_ENOSTR', 60);





define('SOCKET_ENODATA', 61);





define('SOCKET_ETIME', 62);





define('SOCKET_ENOSR', 63);





define('SOCKET_ENONET', 64);





define('SOCKET_EREMOTE', 66);





define('SOCKET_ENOLINK', 67);





define('SOCKET_EADV', 68);





define('SOCKET_ESRMNT', 69);





define('SOCKET_ECOMM', 70);





define('SOCKET_EPROTO', 71);





define('SOCKET_EMULTIHOP', 72);





define('SOCKET_EBADMSG', 74);





define('SOCKET_ENOTUNIQ', 76);





define('SOCKET_EBADFD', 77);





define('SOCKET_EREMCHG', 78);





define('SOCKET_ERESTART', 85);





define('SOCKET_ESTRPIPE', 86);





define('SOCKET_EUSERS', 87);





define('SOCKET_ENOTSOCK', 88);





define('SOCKET_EDESTADDRREQ', 89);





define('SOCKET_EMSGSIZE', 90);





define('SOCKET_EPROTOTYPE', 91);
define('SOCKET_ENOPROTOOPT', 92);





define('SOCKET_EPROTONOSUPPORT', 93);





define('SOCKET_ESOCKTNOSUPPORT', 94);





define('SOCKET_EOPNOTSUPP', 95);





define('SOCKET_EPFNOSUPPORT', 96);





define('SOCKET_EAFNOSUPPORT', 97);
define('SOCKET_EADDRINUSE', 98);





define('SOCKET_EADDRNOTAVAIL', 99);





define('SOCKET_ENETDOWN', 100);





define('SOCKET_ENETUNREACH', 101);





define('SOCKET_ENETRESET', 102);





define('SOCKET_ECONNABORTED', 103);





define('SOCKET_ECONNRESET', 104);





define('SOCKET_ENOBUFS', 105);





define('SOCKET_EISCONN', 106);





define('SOCKET_ENOTCONN', 107);





define('SOCKET_ESHUTDOWN', 108);





define('SOCKET_ETOOMANYREFS', 109);





define('SOCKET_ETIMEDOUT', 110);





define('SOCKET_ECONNREFUSED', 111);





define('SOCKET_EHOSTDOWN', 112);





define('SOCKET_EHOSTUNREACH', 113);





define('SOCKET_EALREADY', 114);





define('SOCKET_EINPROGRESS', 115);





define('SOCKET_EISNAM', 120);





define('SOCKET_EREMOTEIO', 121);





define('SOCKET_EDQUOT', 122);





define('SOCKET_ENOMEDIUM', 123);





define('SOCKET_EMEDIUMTYPE', 124);
define('IPPROTO_IP', 0);
define('IPPROTO_IPV6', 41);
define('SOL_TCP', 6);
define('SOL_UDP', 17);
define('IPV6_UNICAST_HOPS', 16);
define('IPV6_RECVPKTINFO', 49);
define('IPV6_PKTINFO', 50);
define('IPV6_RECVHOPLIMIT', 51);
define('IPV6_HOPLIMIT', 52);
define('IPV6_RECVTCLASS', 66);
define('IPV6_TCLASS', 67);
define('SCM_RIGHTS', 1);
define('SCM_CREDENTIALS', 2);
define('SO_PASSCRED', 16);

define('SOCKET_EPROCLIM', 10067);
define('SOCKET_ESTALE', 10070);
define('SOCKET_EDISCON', 10101);
define('SOCKET_SYSNOTREADY', 10091);
define('SOCKET_VERNOTSUPPORTED', 10092);
define('SOCKET_NOTINITIALISED', 10093);
define('SOCKET_HOST_NOT_FOUND', 11001);
define('SOCKET_TRY_AGAIN', 11002);
define('SOCKET_NO_RECOVERY', 11003);
define('SOCKET_NO_DATA', 11004);
define('SOCKET_NO_ADDRESS', 11004);

define('AI_PASSIVE', 1);
define('AI_CANONNAME', 2);
define('AI_NUMERICHOST', 4);
define('AI_ADDRCONFIG', 32);
define('AI_NUMERICSERV', 1024);
define('AI_V4MAPPED', 8);
define('AI_ALL', 16);




define('TCP_DEFER_ACCEPT', 9);




define('SO_INCOMING_CPU', 49);




define('SO_MEMINFO', 55);




define('SO_BPF_EXTENSIONS', 48);




define('SKF_AD_OFF', -4096);




define('SKF_AD_PROTOCOL', 0);




define('SKF_AD_PKTTYPE', 4);




define('SKF_AD_IFINDEX', 8);




define('SKF_AD_NLATTR', 12);




define('SKF_AD_NLATTR_NEST', 16);




define('SKF_AD_MARK', 20);




define('SKF_AD_QUEUE', 24);




define('SKF_AD_HATYPE', 28);




define('SKF_AD_RXHASH', 32);




define('SKF_AD_CPU', 36);




define('SKF_AD_ALU_XOR_X', 40);




define('SKF_AD_VLAN_TAG', 44);




define('SKF_AD_VLAN_TAG_PRESENT', 48);




define('SKF_AD_PAY_OFFSET', 52);




define('SKF_AD_RANDOM', 56);




define('SKF_AD_VLAN_TPID', 60);




define('SKF_AD_MAX', 64);




define('TCP_CONGESTION', 13);




define('TCP_NOTSENT_LOWAT', 25);




define('TCP_KEEPIDLE', 4);




define('TCP_KEEPINTVL', 5);




define('TCP_KEEPCNT', 6);




final class Socket
{




private function __construct() {}
}




final class AddressInfo
{




private function __construct() {}
}
