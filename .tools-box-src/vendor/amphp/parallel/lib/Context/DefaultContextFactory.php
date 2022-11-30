<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Context;

use _HumbugBoxb47773b41c19\Amp\Promise;
class DefaultContextFactory implements ContextFactory
{
    public function create($script) : Context
    {
        if (Parallel::isSupported()) {
            return new Parallel($script);
        }
        return new Process($script);
    }
    public function run($script) : Promise
    {
        if (Parallel::isSupported()) {
            return Parallel::run($script);
        }
        return Process::run($script);
    }
}
