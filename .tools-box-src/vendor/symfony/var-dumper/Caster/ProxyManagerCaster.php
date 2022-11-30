<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Caster;

use _HumbugBoxb47773b41c19\ProxyManager\Proxy\ProxyInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner\Stub;
class ProxyManagerCaster
{
    public static function castProxy(ProxyInterface $c, array $a, Stub $stub, bool $isNested)
    {
        if ($parent = \get_parent_class($c)) {
            $stub->class .= ' - ' . $parent;
        }
        $stub->class .= '@proxy';
        return $a;
    }
}
