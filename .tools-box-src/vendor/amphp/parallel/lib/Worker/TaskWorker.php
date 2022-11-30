<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker;

use _HumbugBoxb47773b41c19\Amp\Failure;
use _HumbugBoxb47773b41c19\Amp\Parallel\Context\Context;
use _HumbugBoxb47773b41c19\Amp\Parallel\Context\StatusError;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\ChannelException;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Success;
use _HumbugBoxb47773b41c19\Amp\TimeoutException;
use function _HumbugBoxb47773b41c19\Amp\call;
abstract class TaskWorker implements Worker
{
    const SHUTDOWN_TIMEOUT = 1000;
    const ERROR_TIMEOUT = 250;
    private $context;
    private $started = \false;
    private $pending;
    private $exitStatus;
    public function __construct(Context $context)
    {
        if ($context->isRunning()) {
            throw new \Error("The context was already running");
        }
        $this->context = $context;
        $context =& $this->context;
        $pending =& $this->pending;
        \register_shutdown_function(static function () use(&$context, &$pending) : void {
            if ($context === null || !$context->isRunning()) {
                return;
            }
            try {
                Promise\wait(Promise\timeout(call(function () use($context, $pending) : \Generator {
                    if ($pending) {
                        (yield $pending);
                    }
                    (yield $context->send(0));
                    return (yield $context->join());
                }), self::SHUTDOWN_TIMEOUT));
            } catch (\Throwable $exception) {
                if ($context !== null) {
                    $context->kill();
                }
            }
        });
    }
    public function isRunning() : bool
    {
        return !$this->started || $this->exitStatus === null && $this->context !== null && $this->context->isRunning();
    }
    public function isIdle() : bool
    {
        return $this->pending === null;
    }
    public function enqueue(Task $task) : Promise
    {
        if ($this->exitStatus) {
            throw new StatusError("The worker has been shut down");
        }
        $promise = $this->pending = call(function () use($task) : \Generator {
            if ($this->pending) {
                try {
                    (yield $this->pending);
                } catch (\Throwable $exception) {
                }
            }
            if ($this->exitStatus !== null || $this->context === null) {
                throw new WorkerException("The worker was shutdown");
            }
            if (!$this->context->isRunning()) {
                if ($this->started) {
                    throw new WorkerException("The worker crashed");
                }
                $this->started = \true;
                (yield $this->context->start());
            }
            $job = new Internal\Job($task);
            try {
                (yield $this->context->send($job));
                $result = (yield $this->context->receive());
            } catch (ChannelException $exception) {
                try {
                    (yield Promise\timeout($this->context->join(), self::ERROR_TIMEOUT));
                } catch (TimeoutException $timeout) {
                    $this->kill();
                    throw new WorkerException("The worker failed unexpectedly", 0, $exception);
                }
                throw new WorkerException("The worker exited unexpectedly", 0, $exception);
            }
            if (!$result instanceof Internal\TaskResult) {
                $this->kill();
                throw new WorkerException("Context did not return a task result");
            }
            if ($result->getId() !== $job->getId()) {
                $this->kill();
                throw new WorkerException("Task results returned out of order");
            }
            return $result->promise();
        });
        $promise->onResolve(function () use($promise) : void {
            if ($this->pending === $promise) {
                $this->pending = null;
            }
        });
        return $promise;
    }
    public function shutdown() : Promise
    {
        if ($this->exitStatus !== null) {
            return $this->exitStatus;
        }
        if ($this->context === null || !$this->context->isRunning()) {
            return $this->exitStatus = new Success(-1);
        }
        return $this->exitStatus = call(function () : \Generator {
            if ($this->pending) {
                (yield Promise\any([$this->pending]));
            }
            (yield $this->context->send(0));
            try {
                return (yield Promise\timeout($this->context->join(), self::SHUTDOWN_TIMEOUT));
            } catch (\Throwable $exception) {
                $this->context->kill();
                throw new WorkerException("Failed to gracefully shutdown worker", 0, $exception);
            } finally {
                $this->context = null;
                $this->pending = null;
            }
        });
    }
    public function kill() : void
    {
        if ($this->exitStatus !== null || $this->context === null) {
            return;
        }
        if ($this->context->isRunning()) {
            $this->context->kill();
            $this->exitStatus = new Failure(new WorkerException("The worker was killed"));
            return;
        }
        $this->exitStatus = new Success(0);
        $this->context = null;
        $this->pending = null;
    }
}
