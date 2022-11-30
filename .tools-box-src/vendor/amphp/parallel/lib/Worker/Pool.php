<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker;

interface Pool extends Worker
{
    const DEFAULT_MAX_SIZE = 32;
    public function getWorker() : Worker;
    public function getWorkerCount() : int;
    public function getIdleWorkerCount() : int;
    public function getMaxSize() : int;
}
