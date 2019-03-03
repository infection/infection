<?php

namespace Infection\Utils\Traits;

use Infection\Utils\Interfaces\HasContainerInterface;
use Pimple\Psr11\Container;

trait HasContainer
{
    private $container = null;

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function setContainer(Container $container): HasContainerInterface
    {
        $this->container = $container;

        return $this;
    }
}