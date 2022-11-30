<?php







class Judy implements ArrayAccess
{




public const BITSET = 1;





public const INT_TO_INT = 2;





public const INT_TO_MIXED = 3;





public const STRING_TO_INT = 4;





public const STRING_TO_MIXED = 5;







public function __construct($judy_type) {}






public function __destruct() {}








public function byCount($nth_index) {}









public function count($index_start = 0, $index_end = -1) {}








public function first($index = 0) {}








public function firstEmpty($index = 0) {}






public function free() {}







public function getType() {}








public function last($index = -1) {}








public function lastEmpty($index = -1) {}







public function memoryUsage() {}








public function next($index) {}








public function nextEmpty($index) {}








public function offsetExists($offset) {}








public function offsetGet($offset) {}








public function offsetSet($offset, $value) {}







public function offsetUnset($offset) {}








public function prev($index) {}








public function prevEmpty($index) {}










public function size($index_start = 0, $index_end = -1) {}
}


