<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Caster;

use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner\Stub;
class ConstStub extends Stub
{
    public function __construct(string $name, string|int|float $value = null)
    {
        $this->class = $name;
        $this->value = 1 < \func_num_args() ? $value : $name;
    }
    public function __toString() : string
    {
        return (string) $this->value;
    }
}
