<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker\Internal;

use _HumbugBoxb47773b41c19\Amp\Parallel\Worker\Task;
use _HumbugBoxb47773b41c19\Amp\Parallel\Worker\Worker;
use _HumbugBoxb47773b41c19\Amp\Promise;
final class PooledWorker implements Worker
{
    private $push;
    private $worker;
    public function __construct(Worker $worker, callable $push)
    {
        $this->worker = $worker;
        $this->push = $push;
    }
    public function __destruct()
    {
        ($this->push)($this->worker);
    }
    public function isRunning() : bool
    {
        return $this->worker->isRunning();
    }
    public function isIdle() : bool
    {
        return $this->worker->isIdle();
    }
    public function enqueue(Task $task) : Promise
    {
        return $this->worker->enqueue($task);
    }
    public function shutdown() : Promise
    {
        return $this->worker->shutdown();
    }
    public function kill() : void
    {
        $this->worker->kill();
    }
}
