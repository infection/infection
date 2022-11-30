<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Command;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\LogicException;
use _HumbugBox9658796bb9f0\Symfony\Component\Lock\LockFactory;
use _HumbugBox9658796bb9f0\Symfony\Component\Lock\LockInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Lock\Store\FlockStore;
use _HumbugBox9658796bb9f0\Symfony\Component\Lock\Store\SemaphoreStore;
trait LockableTrait
{
    private $lock;
    private function lock(string $name = null, bool $blocking = \false) : bool
    {
        if (!\class_exists(SemaphoreStore::class)) {
            throw new LogicException('To enable the locking feature you must install the symfony/lock component.');
        }
        if (null !== $this->lock) {
            throw new LogicException('A lock is already in place.');
        }
        if (SemaphoreStore::isSupported()) {
            $store = new SemaphoreStore();
        } else {
            $store = new FlockStore();
        }
        $this->lock = (new LockFactory($store))->createLock($name ?: $this->getName());
        if (!$this->lock->acquire($blocking)) {
            $this->lock = null;
            return \false;
        }
        return \true;
    }
    private function release()
    {
        if ($this->lock) {
            $this->lock->release();
            $this->lock = null;
        }
    }
}
