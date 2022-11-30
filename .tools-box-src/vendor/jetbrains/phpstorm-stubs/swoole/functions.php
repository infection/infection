<?php

declare(strict_types=1);






function swoole_version() {}






function swoole_cpu_num() {}




function swoole_last_error() {}







function swoole_async_dns_lookup_coro($domain_name, $timeout = null, $type = null) {}





function swoole_async_set($settings) {}




function swoole_coroutine_create(callable $func, ...$params) {}
















function swoole_coroutine_defer(callable $callback) {}







function swoole_coroutine_socketpair($domain, $type, $protocol) {}






function swoole_test_kernel_coroutine($count = null, $sleep_time = null) {}








function swoole_client_select(&$read_array, &$write_array, &$error_array, $timeout = null) {}








function swoole_select(&$read_array, &$write_array, &$error_array, $timeout = null) {}





function swoole_set_process_name($process_name) {}




function swoole_get_local_ip() {}




function swoole_get_local_mac() {}






function swoole_strerror($errno, $error_type = null) {}




function swoole_errno() {}




function swoole_clear_error() {}




function swoole_error_log(int $level, string $msg) {}





function swoole_error_log_ex(int $level, int $error, string $msg) {}





function swoole_ignore_error(int $error) {}






function swoole_hashcode($data, $type = null) {}






function swoole_mime_type_add($suffix, $mime_type) {}






function swoole_mime_type_set($suffix, $mime_type) {}





function swoole_mime_type_delete($suffix) {}





function swoole_mime_type_get($filename) {}





function swoole_get_mime_type($filename) {}





function swoole_mime_type_exists($filename) {}




function swoole_mime_type_list() {}




function swoole_clear_dns_cache() {}








function swoole_substr_unserialize($str, $offset, $length = null, $options = null) {}










function swoole_substr_json_decode($json, $offset, $length = null, $associative = null, $depth = null, $flags = null) {}




function swoole_internal_call_user_shutdown_begin() {}







function swoole_get_objects() {}







function swoole_get_vm_status() {}





function swoole_get_object_by_handle(int $handle) {}








function go(callable $func, ...$params) {}





















function defer(callable $callback) {}








function swoole_event_add($fd, $read_callback, $write_callback = null, $events = null) {}





function swoole_event_del($fd) {}








function swoole_event_set($fd, $read_callback = null, $write_callback = null, $events = null) {}






function swoole_event_isset($fd, $events = null) {}




function swoole_event_dispatch() {}







function swoole_event_defer(callable $callback) {}






function swoole_event_cycle($callback, $before = null) {}






function swoole_event_write($fd, $data) {}




function swoole_event_wait() {}




function swoole_event_exit() {}







function swoole_timer_set(array $settings) {}







function swoole_timer_after(int $ms, callable $callback, ...$params) {}







function swoole_timer_tick(int $ms, callable $callback, ...$params) {}







function swoole_timer_exists(int $timer_id) {}







function swoole_timer_info(int $timer_id) {}







function swoole_timer_stats() {}







function swoole_timer_list() {}







function swoole_timer_clear(int $timer_id) {}







function swoole_timer_clear_all() {}
