<?php

namespace Infection\Utils\Traits;

use Infection\EventDispatcher\EventDispatcher;
use Infection\Utils\Interfaces\HasDispatcherInterface;

trait HasDispatcher
{
    private $dispatcher = null;

    public function getDispatcher(): EventDispatcher
    {
        return $this->dispatcher;
    }

    public function setDispatcher(EventDispatcher $dispatcher): HasDispatcherInterface
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }
}