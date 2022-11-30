<?php










function uv_unref($uv_t) {}







function uv_last_error($uv_loop = null): int {}







function uv_err_name(int $error_code): string {}







function uv_strerror(int $error_code): string {}






function uv_update_time($uv_loop) {}








function uv_ref($uv_handle) {}








function uv_run($uv_loop = null) {}






function uv_run_once($uv_loop = null) {}








function uv_loop_delete($uv_loop) {}




function uv_now(): int {}









function uv_tcp_bind($uv_tcp, $uv_sockaddr) {}









function uv_tcp_bind6($uv_tcp, $uv_sockaddr) {}










function uv_write($handle, string $data, callable $callback) {}









function uv_write2($handle, string $data, $send, callable $callback) {}







function uv_tcp_nodelay($handle, bool $enable) {}









function uv_accept($server, $client) {}









function uv_shutdown($handle, callable $callback) {}









function uv_close($handle, callable $callback) {}










function uv_read_start($handle, callable $callback) {}







function uv_read2_start($handle, callable $callback) {}








function uv_read_stop($handle) {}









function uv_ip4_addr(string $ipv4_addr, int $port) {}









function uv_ip6_addr(string $ipv6_addr, int $port) {}










function uv_listen($handle, int $backlog, callable $callback) {}










function uv_tcp_connect($handle, $ipv4_addr, callable $callback) {}










function uv_tcp_connect6($handle, $ipv6_addr, callable $callback) {}








function uv_timer_init($loop = null) {}











function uv_timer_start($timer, int $timeout, int $repeat, callable $callback) {}








function uv_timer_stop($timer): int {}








function uv_timer_again($timer) {}









function uv_timer_set_repeat($timer, int $repeat) {}








function uv_timer_get_repeat($timer): int {}








function uv_idle_init($loop = null) {}









function uv_idle_start($idle, callable $callback) {}








function uv_idle_stop($idle) {}










function uv_getaddrinfo($loop, callable $callback, string $node, string $service, array $hints) {}








function uv_tcp_init($loop = null) {}






function uv_default_loop() {}






function uv_loop_new() {}








function uv_udp_init($loop = null) {}










function uv_udp_bind($resource, $address, int $flags) {}










function uv_udp_bind6($resource, $address, int $flags) {}









function uv_udp_recv_start($handle, callable $callback) {}








function uv_udp_recv_stop($handle) {}











function uv_udp_set_membership($handle, string $multicast_addr, string $interface_addr, int $membership): int {}









function uv_udp_set_multicast_loop($handle, int $enabled) {}









function uv_udp_set_multicast_ttl($handle, int $ttl) {}









function uv_udp_set_broadcast($handle, bool $enabled) {}











function uv_udp_send($handle, string $data, $uv_addr, callable $callback) {}











function uv_udp_send6($handle, string $data, $uv_addr6, callable $callback) {}






function uv_is_active($handle): bool {}






function uv_is_readable($handle): bool {}






function uv_is_writable($handle): bool {}








function uv_walk($loop, callable $closure, array $opaque = null): bool {}






function uv_guess_handle($uv): int {}








function uv_handle_type($uv): int {}









function uv_pipe_init($loop, int $ipc) {}









function uv_pipe_open($handle, int $pipe) {}









function uv_pipe_bind($handle, string $name): int {}










function uv_pipe_connect($handle, string $path, callable $callback) {}







function uv_pipe_pending_instances($handle, $count) {}








function uv_ares_init_options($loop, array $options, int $optmask) {}









function ares_gethostbyname($handle, string $name, int $flag, callable $callback) {}








function uv_loadavg(): array {}






function uv_uptime(): float {}






function uv_get_free_memory(): int {}






function uv_get_total_memory(): int {}




function uv_hrtime(): int {}






function uv_exepath(): string {}






function uv_cpu_info(): array {}




function uv_interface_addresses(): array {}







function uv_stdio_new($fd, int $flags) {}














function uv_spawn($loop, string $command, array $args, array $stdio, string $cwd, array $env = [], ?callable $callback = null, ?int $flags = null, ?array $options = null) {}









function uv_process_kill($handle, int $signal) {}







function uv_kill(int $pid, int $signal) {}







function uv_chdir(string $directory): bool {}






function uv_rwlock_init() {}






function uv_rwlock_rdlock($handle) {}






function uv_rwlock_tryrdlock($handle): bool {}








function uv_rwlock_rdunlock($handle) {}








function uv_rwlock_wrlock($handle) {}




function uv_rwlock_trywrlock($handle) {}






function uv_rwlock_wrunlock($handle) {}






function uv_mutex_init() {}








function uv_mutex_lock($lock) {}






function uv_mutex_trylock($lock): bool {}







function uv_sem_init(int $value) {}








function uv_sem_post($sem) {}






function uv_sem_wait($sem) {}






function uv_sem_trywait($sem) {}








function uv_prepare_init($loop) {}









function uv_prepare_start($handle, callable $callback) {}








function uv_prepare_stop($handle) {}








function uv_check_init($loop) {}









function uv_check_start($handle, callable $callback) {}








function uv_check_stop($handle) {}









function uv_async_init($loop, callable $callback) {}








function uv_async_send($handle) {}










function uv_queue_work($loop, callable $callback, callable $after_callback) {}












function uv_fs_open($loop, string $path, int $flag, int $mode, callable $callback) {}












function uv_fs_read($loop, $fd, int $offset, int $length, callable $callback) {}










function uv_fs_close($loop, $fd, callable $callback) {}












function uv_fs_write($loop, $fd, string $buffer, int $offset, callable $callback) {}










function uv_fs_fsync($loop, $fd, callable $callback) {}










function uv_fs_fdatasync($loop, $fd, callable $callback) {}











function uv_fs_ftruncate($loop, $fd, int $offset, callable $callback) {}











function uv_fs_mkdir($loop, string $path, int $mode, callable $callback) {}










function uv_fs_rmdir($loop, string $path, callable $callback) {}










function uv_fs_unlink($loop, string $path, callable $callback) {}











function uv_fs_rename($loop, string $from, string $to, callable $callback) {}












function uv_fs_utime($loop, string $path, int $utime, int $atime, callable $callback) {}












function uv_fs_futime($loop, $fd, int $utime, int $atime, callable $callback) {}











function uv_fs_chmod($loop, string $path, int $mode, callable $callback) {}











function uv_fs_fchmod($loop, $fd, int $mode, callable $callback) {}












function uv_fs_chown($loop, string $path, int $uid, int $gid, callable $callback) {}












function uv_fs_fchown($loop, $fd, int $uid, int $gid, callable $callback) {}











function uv_fs_link($loop, string $from, string $to, callable $callback) {}












function uv_fs_symlink($loop, string $from, string $to, int $flags, callable $callback) {}










function uv_fs_readlink($loop, string $path, callable $callback) {}










function uv_fs_stat($loop, string $path, callable $callback) {}










function uv_fs_lstat($loop, string $path, callable $callback) {}










function uv_fs_fstat($loop, $fd, callable $callback) {}











function uv_fs_readdir($loop, string $path, int $flags, callable $callback) {}











function uv_fs_sendfile($loop, $in_fd, $out_fd, int $offset, int $length, callable $callback) {}











function uv_fs_event_init($loop, string $path, callable $callback, int $flags = 0) {}










function uv_tty_init($loop, $fd, int $readable) {}








function uv_tty_get_winsize($tty, int &$width, int &$height): int {}







function uv_tty_set_mode($tty, int $mode): int {}




function uv_tty_reset_mode() {}






function uv_tcp_getsockname($uv_sockaddr): string {}






function uv_tcp_getpeername($uv_sockaddr): string {}






function uv_udp_getsockname($uv_sockaddr): string {}




function uv_resident_set_memory(): int {}






function uv_ip4_name($address): string {}






function uv_ip6_name($address): string {}









function uv_poll_init($uv_loop, $fd) {}










function uv_poll_start($handle, int $events, callable $callback) {}






function uv_poll_stop($poll) {}






function uv_fs_poll_init($uv_loop = null) {}









function uv_fs_poll_start($handle, $callback, string $path, int $interval) {}






function uv_fs_poll_stop($poll) {}






function uv_stop($uv_loop) {}






function uv_signal_stop($sig_handle): int {}
