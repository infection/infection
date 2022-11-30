<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Swoole;

class Lock
{
    public const FILELOCK = 2;
    public const MUTEX = 3;
    public const SEM = 4;
    public const RWLOCK = 1;
    public const SPINLOCK = 5;
    public $errCode = 0;
    public function __construct(int $type = self::MUTEX, string $filename = '')
    {
    }
    public function lock()
    {
    }
    public function lockwait(float $timeout = 1.0)
    {
    }
    public function trylock()
    {
    }
    public function lock_read()
    {
    }
    public function trylock_read()
    {
    }
    public function unlock()
    {
    }
    public function destroy()
    {
    }
}
