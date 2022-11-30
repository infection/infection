<?php

namespace _HumbugBox9658796bb9f0\Symfony\Contracts\Service;

use _HumbugBox9658796bb9f0\Psr\Container\ContainerInterface;
interface ServiceProviderInterface extends ContainerInterface
{
    public function getProvidedServices() : array;
}
