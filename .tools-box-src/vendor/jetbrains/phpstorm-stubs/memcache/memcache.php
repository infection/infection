<?php



class MemcachePool
{























public function connect($host, $port, $timeout = 1) {}


































































public function addServer($host, $port = 11211, $persistent = true, $weight = null, $timeout = 1, $retry_interval = 15, $status = true, callable $failure_callback = null, $timeoutms = null) {}
































public function setServerParams($host, $port = 11211, $timeout = 1, $retry_interval = 15, $status = true, callable $failure_callback = null) {}

public function setFailureCallback() {}









public function getServerStatus($host, $port = 11211) {}

public function findServer() {}







public function getVersion() {}
















public function add($key, $var, $flag = null, $expire = null) {}














public function set($key, $var, $flag = null, $expire = null) {}











public function replace($key, $var, $flag = null, $expire = null) {}

public function cas() {}

public function append() {}




public function prepend() {}





















public function get($key, &$flags = null) {}









public function delete($key, $timeout = 0) {}




















public function getStats($type = null, $slabid = null, $limit = 100) {}

















public function getExtendedStats($type = null, $slabid = null, $limit = 100) {}









public function setCompressThreshold($thresold, $min_saving = 0.2) {}









public function increment($key, $value = 1) {}









public function decrement($key, $value = 1) {}







public function close() {}







public function flush() {}
}





class Memcache extends MemcachePool
{





















public function pconnect($host, $port, $timeout = 1) {}
}
























function memcache_connect($host, $port, $timeout = 1) {}











function memcache_pconnect($host, $port = null, $timeout = 1) {}

function memcache_add_server() {}

function memcache_set_server_params() {}

function memcache_set_failure_callback() {}

function memcache_get_server_status() {}

function memcache_get_version() {}

function memcache_add() {}

function memcache_set() {}

function memcache_replace() {}

function memcache_cas() {}

function memcache_append() {}

function memcache_prepend() {}

function memcache_get() {}

function memcache_delete() {}












function memcache_debug($on_off) {}

function memcache_get_stats() {}

function memcache_get_extended_stats() {}

function memcache_set_compress_threshold() {}

function memcache_increment() {}

function memcache_decrement() {}

function memcache_close() {}

function memcache_flush() {}

define('MEMCACHE_COMPRESSED', 2);
define('MEMCACHE_USER1', 65536);
define('MEMCACHE_USER2', 131072);
define('MEMCACHE_USER3', 262144);
define('MEMCACHE_USER4', 524288);
define('MEMCACHE_HAVE_SESSION', 1);


