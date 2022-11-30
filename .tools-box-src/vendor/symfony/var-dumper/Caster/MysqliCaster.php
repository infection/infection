<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Caster;

use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner\Stub;
final class MysqliCaster
{
    public static function castMysqliDriver(\mysqli_driver $c, array $a, Stub $stub, bool $isNested) : array
    {
        foreach ($a as $k => $v) {
            if (isset($c->{$k})) {
                $a[$k] = $c->{$k};
            }
        }
        return $a;
    }
}
