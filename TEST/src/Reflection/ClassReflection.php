<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Reflection;

interface ClassReflection
{
    public function hasParentMethodWithVisibility(string $methodName, Visibility $visibility) : bool;
    public function getName() : string;
}
