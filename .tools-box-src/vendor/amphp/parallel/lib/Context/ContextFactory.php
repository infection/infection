<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Context;

use _HumbugBoxb47773b41c19\Amp\Promise;
interface ContextFactory
{
    public function create($script) : Context;
    public function run($script) : Promise;
}
