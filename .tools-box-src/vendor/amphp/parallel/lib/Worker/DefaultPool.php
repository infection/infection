<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker;

use _HumbugBoxb47773b41c19\Amp\Parallel\Context\StatusError;
use _HumbugBoxb47773b41c19\Amp\Promise;
use function _HumbugBoxb47773b41c19\Amp\asyncCall;
final class DefaultPool implements Pool
{
    private $running = \true;
    private $maxSize;
    private $factory;
    private $workers;
    private $idleWorkers;
    private $busyQueue;
    private $push;
    private $exitStatus;
    public function __construct(int $maxSize = self::DEFAULT_MAX_SIZE, WorkerFactory $factory = null)
    {
        if ($maxSize < 0) {
            throw new \Error("Maximum size must be a non-negative integer");
        }
        $this->maxSize = $maxSize;
        $this->factory = $factory ?: factory();
        $this->workers = new \SplObjectStorage();
        $this->idleWorkers = new \SplQueue();
        $this->busyQueue = new \SplQueue();
        $workers = $this->workers;
        $idleWorkers = $this->idleWorkers;
        $busyQueue = $this->busyQueue;
        $this->push = static function (Worker $worker) use($workers, $idleWorkers, $busyQueue) : void {
            if (!$workers->contains($worker) || ($workers[$worker] -= 1) > 0) {
                return;
            }
            foreach ($busyQueue as $key => $busy) {
                if ($busy === $worker) {
                    unset($busyQueue[$key]);
                    break;
                }
            }
            $idleWorkers->push($worker);
        };
    }
    public function __destruct()
    {
        if ($this->isRunning()) {
            $this->kill();
        }
    }
    public function isRunning() : bool
    {
        return $this->running;
    }
    public function isIdle() : bool
    {
        return $this->idleWorkers->count() > 0 || $this->workers->count() === 0;
    }
    public function getMaxSize() : int
    {
        return $this->maxSize;
    }
    public function getWorkerCount() : int
    {
        return $this->workers->count();
    }
    public function getIdleWorkerCount() : int
    {
        return $this->idleWorkers->count();
    }
    public function enqueue(Task $task) : Promise
    {
        $worker = $this->pull();
        $promise = $worker->enqueue($task);
        $promise->onResolve(function () use($worker) : void {
            ($this->push)($worker);
        });
        return $promise;
    }
    public function shutdown() : Promise
    {
        if ($this->exitStatus) {
            return $this->exitStatus;
        }
        $this->running = \false;
        $shutdowns = [];
        foreach ($this->workers as $worker) {
            if ($worker->isRunning()) {
                $shutdowns[] = $worker->shutdown();
            }
        }
        return $this->exitStatus = Promise\all($shutdowns);
    }
    public function kill() : void
    {
        $this->running = \false;
        foreach ($this->workers as $worker) {
            \assert($worker instanceof Worker);
            if ($worker->isRunning()) {
                $worker->kill();
            }
        }
    }
    public function getWorker() : Worker
    {
        return new Internal\PooledWorker($this->pull(), $this->push);
    }
    private function pull() : Worker
    {
        if (!$this->isRunning()) {
            throw new StatusError("The pool was shutdown");
        }
        do {
            if ($this->idleWorkers->isEmpty()) {
                if ($this->getWorkerCount() >= $this->maxSize) {
                    $worker = $this->busyQueue->shift();
                } else {
                    $worker = $this->factory->create();
                    if (!$worker->isRunning()) {
                        throw new WorkerException('Worker factory did not create a viable worker');
                    }
                    $this->workers->attach($worker, 0);
                    break;
                }
            } else {
                $worker = $this->idleWorkers->shift();
            }
            \assert($worker instanceof Worker);
            if ($worker->isRunning()) {
                break;
            }
            asyncCall(function () use($worker) : \Generator {
                try {
                    $code = (yield $worker->shutdown());
                    \trigger_error('Worker in pool exited unexpectedly with code ' . $code, \E_USER_WARNING);
                } catch (\Throwable $exception) {
                    \trigger_error('Worker in pool crashed with exception on shutdown: ' . $exception->getMessage(), \E_USER_WARNING);
                }
            });
            $this->workers->detach($worker);
        } while (\true);
        $this->busyQueue->push($worker);
        $this->workers[$worker] += 1;
        return $worker;
    }
}
