<?php

namespace Infection\Plugin;

use Infection\Utils\Interfaces\HasContainerInterface;
use Pimple\Psr11\Container;

interface PluginInterface extends HasContainerInterface
{
    public function __construct(Container $container);
    public function initialize();
}