<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker;

use _HumbugBoxb47773b41c19\Amp\Loop;
use _HumbugBoxb47773b41c19\Amp\Struct;
final class BasicEnvironment implements Environment
{
    private $data = [];
    private $queue;
    private $timer;
    public function __construct()
    {
        $this->queue = $queue = new \SplPriorityQueue();
        $data =& $this->data;
        $this->timer = Loop::repeat(1000, static function (string $watcherId) use($queue, &$data) : void {
            $time = \time();
            while (!$queue->isEmpty()) {
                list($key, $expiration) = $queue->top();
                if (!isset($data[$key])) {
                    $queue->extract();
                    continue;
                }
                $struct = $data[$key];
                if ($struct->expire === 0) {
                    $queue->extract();
                    continue;
                }
                if ($struct->expire !== $expiration) {
                    $queue->extract();
                    continue;
                }
                if ($time < $struct->expire) {
                    break;
                }
                unset($data[$key]);
                $queue->extract();
            }
            if ($queue->isEmpty()) {
                Loop::disable($watcherId);
            }
        });
        Loop::disable($this->timer);
        Loop::unreference($this->timer);
    }
    public function exists(string $key) : bool
    {
        return isset($this->data[$key]);
    }
    public function get(string $key)
    {
        if (!isset($this->data[$key])) {
            return null;
        }
        $struct = $this->data[$key];
        if ($struct->ttl !== null) {
            $expire = \time() + $struct->ttl;
            if ($struct->expire < $expire) {
                $struct->expire = $expire;
                $this->queue->insert([$key, $struct->expire], -$struct->expire);
            }
        }
        return $struct->data;
    }
    public function set(string $key, $value, int $ttl = null) : void
    {
        if ($value === null) {
            $this->delete($key);
            return;
        }
        if ($ttl !== null && $ttl <= 0) {
            throw new \Error("The time-to-live must be a positive integer or null");
        }
        $struct = new class
        {
            use Struct;
            public $data;
            public $expire = 0;
            public $ttl;
        };
        $struct->data = $value;
        if ($ttl !== null) {
            $struct->ttl = $ttl;
            $struct->expire = \time() + $ttl;
            $this->queue->insert([$key, $struct->expire], -$struct->expire);
            Loop::enable($this->timer);
        }
        $this->data[$key] = $struct;
    }
    public function delete(string $key) : void
    {
        unset($this->data[$key]);
    }
    public function offsetExists($key) : bool
    {
        return $this->exists($key);
    }
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->get($key);
    }
    public function offsetSet($key, $value) : void
    {
        $this->set($key, $value);
    }
    public function offsetUnset($key) : void
    {
        $this->delete($key);
    }
    public function clear() : void
    {
        $this->data = [];
        Loop::disable($this->timer);
        $this->queue = new \SplPriorityQueue();
    }
}
