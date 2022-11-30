<?php

namespace _HumbugBoxb47773b41c19\Amp\Sync;

use _HumbugBoxb47773b41c19\Amp\Coroutine;
use _HumbugBoxb47773b41c19\Amp\Delayed;
use _HumbugBoxb47773b41c19\Amp\Promise;
class PosixSemaphore implements Semaphore
{
    public const LATENCY_TIMEOUT = 10;
    private $id;
    private $key;
    private $initializer = 0;
    private $queue;
    public static function create(string $id, int $maxLocks, int $permissions = 0600) : self
    {
        if ($maxLocks < 1) {
            throw new \Error('Number of locks must be greater than 0');
        }
        $semaphore = new self($id);
        $semaphore->init($maxLocks, $permissions);
        return $semaphore;
    }
    public static function use(string $id) : self
    {
        $semaphore = new self($id);
        $semaphore->open();
        return $semaphore;
    }
    private function __construct(string $id)
    {
        if (!\extension_loaded("sysvmsg")) {
            throw new \Error(__CLASS__ . " requires the sysvmsg extension.");
        }
        $this->id = $id;
        $this->key = self::makeKey($this->id);
    }
    private function __clone()
    {
    }
    public function __sleep()
    {
        throw new \Error('A semaphore cannot be serialized!');
    }
    public function getId() : string
    {
        return $this->id;
    }
    private function open() : void
    {
        if (!\msg_queue_exists($this->key)) {
            throw new SyncException('No semaphore with that ID found');
        }
        $this->queue = \msg_get_queue($this->key);
        if (!$this->queue) {
            throw new SyncException('Failed to open the semaphore.');
        }
    }
    private function init(int $maxLocks, int $permissions) : void
    {
        if (\msg_queue_exists($this->key)) {
            throw new SyncException('A semaphore with that ID already exists');
        }
        $this->queue = \msg_get_queue($this->key, $permissions);
        if (!$this->queue) {
            throw new SyncException('Failed to create the semaphore.');
        }
        $this->initializer = \getmypid();
        while (--$maxLocks >= 0) {
            $this->release($maxLocks);
        }
    }
    public function getPermissions() : int
    {
        $stat = \msg_stat_queue($this->queue);
        return $stat['msg_perm.mode'];
    }
    public function setPermissions(int $mode)
    {
        if (!\msg_set_queue($this->queue, ['msg_perm.mode' => $mode])) {
            throw new SyncException('Failed to change the semaphore permissions.');
        }
    }
    public function acquire() : Promise
    {
        return new Coroutine($this->doAcquire());
    }
    private function doAcquire() : \Generator
    {
        do {
            if (@\msg_receive($this->queue, 0, $type, 1, $id, \false, \MSG_IPC_NOWAIT, $errno)) {
                return new Lock(\unpack("C", $id)[1], function (Lock $lock) {
                    $this->release($lock->getId());
                });
            }
            if ($errno !== \MSG_ENOMSG) {
                throw new SyncException(\sprintf('Failed to acquire a lock; errno: %d', $errno));
            }
        } while ((yield new Delayed(self::LATENCY_TIMEOUT, \true)));
    }
    public function __destruct()
    {
        if ($this->initializer === 0 || $this->initializer !== \getmypid()) {
            return;
        }
        if (\PHP_VERSION_ID < 80000 && (!\is_resource($this->queue) || !\msg_queue_exists($this->key))) {
            return;
        }
        if (\PHP_VERSION_ID >= 80000 && (!$this->queue instanceof \SysvMessageQueue || !\msg_queue_exists($this->key))) {
            return;
        }
        \msg_remove_queue($this->queue);
    }
    protected function release(int $id)
    {
        if (!$this->queue) {
            return;
        }
        if (!@\msg_send($this->queue, 1, \pack("C", $id), \false, \false, $errno)) {
            if ($errno === \MSG_EAGAIN) {
                throw new SyncException('The semaphore size is larger than the system allows.');
            }
            throw new SyncException('Failed to release the lock.');
        }
    }
    private static function makeKey(string $id) : int
    {
        return \abs(\unpack("l", \md5($id, \true))[1]);
    }
}
