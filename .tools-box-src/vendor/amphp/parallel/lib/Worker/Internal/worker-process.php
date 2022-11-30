<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker\Internal;

use _HumbugBoxb47773b41c19\Amp\Parallel\Sync;
use _HumbugBoxb47773b41c19\Amp\Parallel\Worker;
use _HumbugBoxb47773b41c19\Amp\Promise;
return function (Sync\Channel $channel) use($argc, $argv) : Promise {
    if (!\defined("AMP_WORKER")) {
        \define("AMP_WORKER", \AMP_CONTEXT);
    }
    if (isset($argv[2])) {
        if (!\is_file($argv[2])) {
            throw new \Error(\sprintf("No file found at bootstrap file path given '%s'", $argv[2]));
        }
        (function () use($argc, $argv) : void {
            require $argv[2];
        })();
    }
    if (!isset($argv[1])) {
        throw new \Error("No environment class name provided");
    }
    $className = $argv[1];
    if (!\class_exists($className)) {
        throw new \Error(\sprintf("Invalid environment class name '%s'", $className));
    }
    if (!\is_subclass_of($className, Worker\Environment::class)) {
        throw new \Error(\sprintf("The class '%s' does not implement '%s'", $className, Worker\Environment::class));
    }
    $environment = new $className();
    $runner = new Worker\TaskRunner($channel, $environment);
    return $runner->run();
};
