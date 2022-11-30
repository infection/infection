<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Caster;

use Ds\Collection;
use Ds\Map;
use Ds\Pair;
use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner\Stub;
class DsCaster
{
    public static function castCollection(Collection $c, array $a, Stub $stub, bool $isNested) : array
    {
        $a[Caster::PREFIX_VIRTUAL . 'count'] = $c->count();
        $a[Caster::PREFIX_VIRTUAL . 'capacity'] = $c->capacity();
        if (!$c instanceof Map) {
            $a += $c->toArray();
        }
        return $a;
    }
    public static function castMap(Map $c, array $a, Stub $stub, bool $isNested) : array
    {
        foreach ($c as $k => $v) {
            $a[] = new DsPairStub($k, $v);
        }
        return $a;
    }
    public static function castPair(Pair $c, array $a, Stub $stub, bool $isNested) : array
    {
        foreach ($c->toArray() as $k => $v) {
            $a[Caster::PREFIX_VIRTUAL . $k] = $v;
        }
        return $a;
    }
    public static function castPairStub(DsPairStub $c, array $a, Stub $stub, bool $isNested) : array
    {
        if ($isNested) {
            $stub->class = Pair::class;
            $stub->value = null;
            $stub->handle = 0;
            $a = $c->value;
        }
        return $a;
    }
}
