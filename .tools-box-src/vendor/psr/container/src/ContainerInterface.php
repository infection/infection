<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Psr\Container;

interface ContainerInterface
{
    public function get(string $id);
    public function has(string $id) : bool;
}
