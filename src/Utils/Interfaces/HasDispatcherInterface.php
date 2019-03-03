<?php

namespace Infection\Utils\Interfaces;

use Infection\EventDispatcher\EventDispatcher;

interface HasDispatcherInterface
{
    public function getDispatcher(): EventDispatcher;
    public function setDispatcher(EventDispatcher $dispatcher): self;
}