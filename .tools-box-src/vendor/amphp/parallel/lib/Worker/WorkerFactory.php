<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker;

interface WorkerFactory
{
    public function create() : Worker;
}
