<?php

namespace _HumbugBoxb47773b41c19\Amp\Sync;

class Lock
{
    private $releaser;
    private $id;
    public function __construct(int $id, callable $releaser)
    {
        $this->id = $id;
        $this->releaser = $releaser;
    }
    public function isReleased() : bool
    {
        return !$this->releaser;
    }
    public function getId() : int
    {
        return $this->id;
    }
    public function release()
    {
        if (!$this->releaser) {
            return;
        }
        $releaser = $this->releaser;
        $this->releaser = null;
        $releaser($this);
    }
    public function __destruct()
    {
        if (!$this->isReleased()) {
            $this->release();
        }
    }
}
