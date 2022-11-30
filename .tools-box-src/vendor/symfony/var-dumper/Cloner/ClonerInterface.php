<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner;

interface ClonerInterface
{
    public function cloneVar(mixed $var) : Data;
}
