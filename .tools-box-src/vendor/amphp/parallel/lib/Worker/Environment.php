<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker;

interface Environment extends \ArrayAccess
{
    public function exists(string $key) : bool;
    public function get(string $key);
    public function set(string $key, $value, int $ttl = null);
    public function delete(string $key);
    public function clear();
}
