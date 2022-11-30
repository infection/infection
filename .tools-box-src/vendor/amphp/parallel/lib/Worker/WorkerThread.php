<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker;

use _HumbugBoxb47773b41c19\Amp\Parallel\Context\Thread;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\Channel;
use _HumbugBoxb47773b41c19\Amp\Promise;
final class WorkerThread extends TaskWorker
{
    public function __construct(string $envClassName = BasicEnvironment::class, string $bootstrapPath = null)
    {
        parent::__construct(new Thread(static function (Channel $channel, string $className, string $bootstrapPath = null) : Promise {
            if ($bootstrapPath !== null) {
                if (!\is_file($bootstrapPath)) {
                    throw new \Error(\sprintf("No file found at bootstrap file path given '%s'", $bootstrapPath));
                }
                (static function () use($bootstrapPath) : void {
                    require $bootstrapPath;
                })->bindTo(null, null)();
            }
            if (!\class_exists($className)) {
                throw new \Error(\sprintf("Invalid environment class name '%s'", $className));
            }
            if (!\is_subclass_of($className, Environment::class)) {
                throw new \Error(\sprintf("The class '%s' does not implement '%s'", $className, Environment::class));
            }
            $environment = new $className();
            if (!\defined("AMP_WORKER")) {
                \define("AMP_WORKER", \AMP_CONTEXT);
            }
            $runner = new TaskRunner($channel, $environment);
            return $runner->run();
        }, $envClassName, $bootstrapPath));
    }
}
