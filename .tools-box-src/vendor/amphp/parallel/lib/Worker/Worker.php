<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker;

use _HumbugBoxb47773b41c19\Amp\Promise;
interface Worker
{
    public function isRunning() : bool;
    public function isIdle() : bool;
    public function enqueue(Task $task) : Promise;
    public function shutdown() : Promise;
    public function kill();
}
