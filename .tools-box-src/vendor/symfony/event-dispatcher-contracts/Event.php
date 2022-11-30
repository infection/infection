<?php

namespace _HumbugBoxb47773b41c19\Symfony\Contracts\EventDispatcher;

use _HumbugBoxb47773b41c19\Psr\EventDispatcher\StoppableEventInterface;
class Event implements StoppableEventInterface
{
    private bool $propagationStopped = \false;
    public function isPropagationStopped() : bool
    {
        return $this->propagationStopped;
    }
    public function stopPropagation() : void
    {
        $this->propagationStopped = \true;
    }
}
