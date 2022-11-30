<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Reflection;

final class NullReflection implements ClassReflection
{
    public function hasParentMethodWithVisibility(string $methodName, Visibility $visibility) : bool
    {
        return \false;
    }
    public function getName() : string
    {
        return '';
    }
}
