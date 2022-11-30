<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator;

trait GetMutatorName
{
    public final function getName() : string
    {
        return MutatorFactory::getMutatorNameForClassName(static::class);
    }
}
