<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Caster;

use _HumbugBoxb47773b41c19\Ramsey\Uuid\UuidInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner\Stub;
final class UuidCaster
{
    public static function castRamseyUuid(UuidInterface $c, array $a, Stub $stub, bool $isNested) : array
    {
        $a += [Caster::PREFIX_VIRTUAL . 'uuid' => (string) $c];
        return $a;
    }
}
