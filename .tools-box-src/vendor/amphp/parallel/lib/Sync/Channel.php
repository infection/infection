<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Sync;

use _HumbugBoxb47773b41c19\Amp\Promise;
interface Channel
{
    public function receive() : Promise;
    public function send($data) : Promise;
}
