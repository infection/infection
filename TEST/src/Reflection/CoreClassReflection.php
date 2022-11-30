<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Reflection;

use ReflectionClass;
use ReflectionException;
final class CoreClassReflection implements ClassReflection
{
    private function __construct(private ReflectionClass $reflectionClass)
    {
    }
    public static function fromClassName(string $className) : self
    {
        return new self(new ReflectionClass($className));
    }
    public function hasParentMethodWithVisibility(string $methodName, Visibility $visibility) : bool
    {
        try {
            $method = $this->reflectionClass->getMethod($methodName)->getPrototype();
        } catch (ReflectionException) {
            return \false;
        }
        if ($visibility->isPublic()) {
            return $method->isPublic();
        }
        return $method->isProtected();
    }
    public function getName() : string
    {
        return $this->reflectionClass->getName();
    }
}
