<?php

namespace Infection\Utils\Interfaces;

use Infection\Console\InfectionContainer;
use Pimple\Psr11\Container;

interface HasContainerInterface
{
    public function getContainer(): Container;
    public function setContainer(Container $container): self;
}