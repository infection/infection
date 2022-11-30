<?php

define("LEVELDB_NO_COMPRESSION", 0);

define("LEVELDB_SNAPPY_COMPRESSION", 1);

class LevelDB
{






public function __construct($name, array $options = [
'create_if_missing' => true, 
'error_if_exists' => false, 
'paranoid_checks' => false,
'block_cache_size' => 8 * (2 << 20),
'write_buffer_size' => 4 << 20,
'block_size' => 4096,
'max_open_files' => 1000,
'block_restart_interval' => 16,
'compression' => LEVELDB_SNAPPY_COMPRESSION,
'comparator' => null, 
], array $read_options = [
'verify_check_sum' => false, 
'fill_cache' => true, 
], array $write_options = [

'sync' => false
]) {}







public function get($key, array $read_options = []) {}








public function set($key, $value, array $write_options = []) {}






public function put($key, $value, array $write_options = []) {}







public function delete($key, array $write_options = []) {}







public function write(LevelDBWriteBatch $batch, array $write_options = []) {}











public function getProperty($name) {}

public function getApproximateSizes($start, $limit) {}

public function compactRange($start, $limit) {}

public function close() {}






public function getIterator(array $options = []) {}




public function getSnapshot() {}

public static function destroy($name, array $options = []) {}

public static function repair($name, array $options = []) {}
}

class LevelDBIterator implements Iterator
{
public function __construct(LevelDB $db, array $read_options = []) {}

public function valid() {}

public function rewind() {}

public function last() {}

public function seek($key) {}

public function next() {}

public function prev() {}

public function key() {}

public function current() {}

public function getError() {}

public function destroy() {}
}

class LevelDBWriteBatch
{
public function __construct() {}

public function set($key, $value, array $write_options = []) {}

public function put($key, $value, array $write_options = []) {}

public function delete($key, array $write_options = []) {}

public function clear() {}
}

class LevelDBSnapshot
{
public function __construct(LevelDB $db) {}

public function release() {}
}

class LevelDBException extends Exception {}
