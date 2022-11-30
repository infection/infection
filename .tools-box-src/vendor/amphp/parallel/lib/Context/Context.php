<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Context;

use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\Channel;
use _HumbugBoxb47773b41c19\Amp\Promise;
interface Context extends Channel
{
    public function isRunning() : bool;
    public function start() : Promise;
    public function kill();
    public function join() : Promise;
}
