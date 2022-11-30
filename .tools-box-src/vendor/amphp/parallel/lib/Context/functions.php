<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Context;

use _HumbugBoxb47773b41c19\Amp\Loop;
use _HumbugBoxb47773b41c19\Amp\Promise;
const LOOP_FACTORY_IDENTIFIER = ContextFactory::class;
function create($script) : Context
{
    return factory()->create($script);
}
function run($script) : Promise
{
    return factory()->run($script);
}
function factory(?ContextFactory $factory = null) : ContextFactory
{
    if ($factory === null) {
        $factory = Loop::getState(LOOP_FACTORY_IDENTIFIER);
        if ($factory) {
            return $factory;
        }
        $factory = new DefaultContextFactory();
    }
    Loop::setState(LOOP_FACTORY_IDENTIFIER, $factory);
    return $factory;
}
