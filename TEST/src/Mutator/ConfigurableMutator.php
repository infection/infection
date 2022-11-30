<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator;

/**
@template
@extends
*/
interface ConfigurableMutator extends Mutator
{
    public static function getConfigClassName() : string;
}
