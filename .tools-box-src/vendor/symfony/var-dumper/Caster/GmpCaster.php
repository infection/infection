<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Caster;

use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner\Stub;
class GmpCaster
{
    public static function castGmp(\GMP $gmp, array $a, Stub $stub, bool $isNested, int $filter) : array
    {
        $a[Caster::PREFIX_VIRTUAL . 'value'] = new ConstStub(\gmp_strval($gmp), \gmp_strval($gmp));
        return $a;
    }
}
