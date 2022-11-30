<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Swoole;

class Table implements \Iterator, \ArrayAccess, \Countable
{
    public const TYPE_INT = 1;
    public const TYPE_STRING = 3;
    public const TYPE_FLOAT = 2;
    public $size;
    public $memorySize;
    public function __construct(int $table_size, float $conflict_proportion = 0.2)
    {
    }
    public function column(string $name, int $type, int $size = 0)
    {
    }
    public function create()
    {
    }
    public function destroy()
    {
    }
    public function set(string $key, array $value)
    {
    }
    public function stats()
    {
    }
    public function get(string $key, string $field = null)
    {
    }
    public function del(string $key)
    {
    }
    public function delete(string $key)
    {
    }
    public function exists(string $key)
    {
    }
    public function exist(string $key)
    {
    }
    public function incr(string $key, string $column, $incrby = 1)
    {
    }
    public function decr(string $key, string $column, $decrby = 1)
    {
    }
    public function getSize()
    {
    }
    public function getMemorySize()
    {
    }
    public function current()
    {
    }
    public function key()
    {
    }
    public function next()
    {
    }
    public function rewind()
    {
    }
    public function valid()
    {
    }
    public function offsetExists($offset)
    {
    }
    public function offsetGet($offset)
    {
    }
    public function offsetSet($offset, $value)
    {
    }
    public function offsetUnset($offset)
    {
    }
    public function count()
    {
    }
}
