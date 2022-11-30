<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Sync;

use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Serialization\NativeSerializer;
use _HumbugBoxb47773b41c19\Amp\Serialization\Serializer;
use _HumbugBoxb47773b41c19\Amp\Sync\Lock;
use _HumbugBoxb47773b41c19\Amp\Sync\PosixSemaphore;
use _HumbugBoxb47773b41c19\Amp\Sync\SyncException;
use function _HumbugBoxb47773b41c19\Amp\call;
final class SharedMemoryParcel implements Parcel
{
    const MEM_DATA_OFFSET = 7;
    const STATE_UNALLOCATED = 0;
    const STATE_ALLOCATED = 1;
    const STATE_MOVED = 2;
    const STATE_FREED = 3;
    private $id;
    private $key;
    private $semaphore;
    private $handle;
    private $initializer = 0;
    private $serializer;
    public static function create(string $id, $value, int $size = 8192, int $permissions = 0600, ?Serializer $serializer = null) : self
    {
        $parcel = new self($id, $serializer);
        $parcel->init($value, $size, $permissions);
        return $parcel;
    }
    public static function use(string $id, ?Serializer $serializer = null) : self
    {
        $parcel = new self($id, $serializer);
        $parcel->open();
        return $parcel;
    }
    private function __construct(string $id, ?Serializer $serializer = null)
    {
        if (!\extension_loaded("shmop")) {
            throw new \Error(__CLASS__ . " requires the shmop extension");
        }
        $this->id = $id;
        $this->key = self::makeKey($this->id);
        $this->serializer = $serializer ?? new NativeSerializer();
    }
    private function init($value, int $size = 8192, int $permissions = 0600) : void
    {
        if ($size <= 0) {
            throw new \Error('The memory size must be greater than 0');
        }
        if ($permissions <= 0 || $permissions > 0777) {
            throw new \Error('Invalid permissions');
        }
        $this->semaphore = PosixSemaphore::create($this->id, 1);
        $this->initializer = \getmypid();
        $this->memOpen($this->key, 'n', $permissions, $size + self::MEM_DATA_OFFSET);
        $this->setHeader(self::STATE_ALLOCATED, 0, $permissions);
        $this->wrap($value);
    }
    private function open() : void
    {
        $this->semaphore = PosixSemaphore::use($this->id);
        $this->memOpen($this->key, 'w', 0, 0);
    }
    private function isFreed() : bool
    {
        if ($this->handle !== null) {
            $this->handleMovedMemory();
            $header = $this->getHeader();
            return $header['state'] === static::STATE_FREED;
        }
        return \true;
    }
    public function unwrap() : Promise
    {
        return call(function () {
            $lock = (yield $this->semaphore->acquire());
            \assert($lock instanceof Lock);
            try {
                return $this->getValue();
            } finally {
                $lock->release();
            }
        });
    }
    private function getValue()
    {
        if ($this->isFreed()) {
            throw new SharedMemoryException('The object has already been freed');
        }
        $header = $this->getHeader();
        if ($header['state'] !== self::STATE_ALLOCATED || $header['size'] <= 0) {
            throw new SharedMemoryException('Shared object memory is corrupt');
        }
        $data = $this->memGet(self::MEM_DATA_OFFSET, $header['size']);
        return $this->serializer->unserialize($data);
    }
    private function wrap($value) : void
    {
        if ($this->isFreed()) {
            throw new SharedMemoryException('The object has already been freed');
        }
        $serialized = $this->serializer->serialize($value);
        $size = \strlen($serialized);
        $header = $this->getHeader();
        if (\shmop_size($this->handle) < $size + self::MEM_DATA_OFFSET) {
            $this->key = $this->key < 0xffffffff ? $this->key + 1 : \random_int(0x10, 0xfffffffe);
            $this->setHeader(self::STATE_MOVED, $this->key, 0);
            $this->memDelete();
            \shmop_close($this->handle);
            $this->memOpen($this->key, 'n', $header['permissions'], $size * 2);
        }
        $this->setHeader(self::STATE_ALLOCATED, $size, $header['permissions']);
        $this->memSet(self::MEM_DATA_OFFSET, $serialized);
    }
    public function synchronized(callable $callback) : Promise
    {
        return call(function () use($callback) : \Generator {
            $lock = (yield $this->semaphore->acquire());
            \assert($lock instanceof Lock);
            try {
                $result = (yield call($callback, $this->getValue()));
                if ($result !== null) {
                    $this->wrap($result);
                }
            } finally {
                $lock->release();
            }
            return $result;
        });
    }
    public function __destruct()
    {
        if ($this->initializer === 0 || $this->initializer !== \getmypid()) {
            return;
        }
        if ($this->isFreed()) {
            return;
        }
        $this->setHeader(static::STATE_FREED, 0, 0);
        $this->memDelete();
        \shmop_close($this->handle);
        $this->handle = null;
        $this->semaphore = null;
    }
    private function __clone()
    {
    }
    public function __sleep()
    {
        throw new \Error('A shared memory parcel cannot be serialized!');
    }
    private function handleMovedMemory() : void
    {
        while (\true) {
            $header = $this->getHeader();
            if ($header['state'] !== self::STATE_MOVED) {
                break;
            }
            \shmop_close($this->handle);
            $this->key = $header['size'];
            $this->memOpen($this->key, 'w', 0, 0);
        }
    }
    private function getHeader() : array
    {
        $data = $this->memGet(0, self::MEM_DATA_OFFSET);
        return \unpack('Cstate/Lsize/Spermissions', $data);
    }
    private function setHeader(int $state, int $size, int $permissions) : void
    {
        $header = \pack('CLS', $state, $size, $permissions);
        $this->memSet(0, $header);
    }
    private function memOpen(int $key, string $mode, int $permissions, int $size) : void
    {
        $handle = @\shmop_open($key, $mode, $permissions, $size);
        if ($handle === \false) {
            $error = \error_get_last();
            throw new SharedMemoryException('Failed to create shared memory block: ' . ($error['message'] ?? 'unknown error'));
        }
        $this->handle = $handle;
    }
    private function memGet(int $offset, int $size) : string
    {
        $data = \shmop_read($this->handle, $offset, $size);
        if ($data === \false) {
            $error = \error_get_last();
            throw new SharedMemoryException('Failed to read from shared memory block: ' . ($error['message'] ?? 'unknown error'));
        }
        return $data;
    }
    private function memSet(int $offset, string $data) : void
    {
        if (!\shmop_write($this->handle, $data, $offset)) {
            $error = \error_get_last();
            throw new SharedMemoryException('Failed to write to shared memory block: ' . ($error['message'] ?? 'unknown error'));
        }
    }
    private function memDelete() : void
    {
        if (!\shmop_delete($this->handle)) {
            $error = \error_get_last();
            throw new SharedMemoryException('Failed to discard shared memory block' . ($error['message'] ?? 'unknown error'));
        }
    }
    private static function makeKey(string $id) : int
    {
        return \abs(\unpack("l", \md5($id, \true))[1]);
    }
}
