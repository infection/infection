<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Psr\Container;

interface ContainerInterface
{
    public function get(string $id);
    public function has(string $id);
}
