<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker;

use _HumbugBoxb47773b41c19\Amp\Parallel\Context\Parallel;
use _HumbugBoxb47773b41c19\Amp\Parallel\Context\Thread;
final class DefaultWorkerFactory implements WorkerFactory
{
    private $className;
    public function __construct(string $envClassName = BasicEnvironment::class)
    {
        if (!\class_exists($envClassName)) {
            throw new \Error(\sprintf("Invalid environment class name '%s'", $envClassName));
        }
        if (!\is_subclass_of($envClassName, Environment::class)) {
            throw new \Error(\sprintf("The class '%s' does not implement '%s'", $envClassName, Environment::class));
        }
        $this->className = $envClassName;
    }
    public function create() : Worker
    {
        if (Parallel::isSupported()) {
            return new WorkerParallel($this->className);
        }
        if (Thread::isSupported()) {
            return new WorkerThread($this->className);
        }
        return new WorkerProcess($this->className, [], \getenv("AMP_PHP_BINARY") ?: (\defined("AMP_PHP_BINARY") ? \AMP_PHP_BINARY : null));
    }
}
