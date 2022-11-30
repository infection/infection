<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Caster;

class CutArrayStub extends CutStub
{
    public $preservedSubset;
    public function __construct(array $value, array $preservedKeys)
    {
        parent::__construct($value);
        $this->preservedSubset = \array_intersect_key($value, \array_flip($preservedKeys));
        $this->cut -= \count($this->preservedSubset);
    }
}
