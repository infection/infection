<?php

namespace _HumbugBoxb47773b41c19\Symfony\Contracts\Service;

use _HumbugBoxb47773b41c19\Psr\Container\ContainerInterface;
/**
@template
*/
interface ServiceProviderInterface extends ContainerInterface
{
    public function get(string $id) : mixed;
    public function has(string $id) : bool;
    public function getProvidedServices() : array;
}
