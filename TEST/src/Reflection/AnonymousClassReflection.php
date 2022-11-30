<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Reflection;

use ReflectionClass;
final class AnonymousClassReflection implements ClassReflection
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
        if (self::hasMethodRecursively($this->reflectionClass, $methodName, $visibility)) {
            return \true;
        }
        if ($visibility->isProtected()) {
            return \false;
        }
        foreach ($this->reflectionClass->getInterfaces() as $reflectionInterface) {
            if (self::hasMethod($reflectionInterface, $methodName, $visibility)) {
                return \true;
            }
        }
        return \false;
    }
    public function getName() : string
    {
        return '';
    }
    private static function hasMethodRecursively(ReflectionClass $reflectionClass, string $methodName, Visibility $visibility) : bool
    {
        if (self::hasMethod($reflectionClass, $methodName, $visibility)) {
            return \true;
        }
        $parent = $reflectionClass->getParentClass();
        if ($parent === \false) {
            return \false;
        }
        return self::hasMethodRecursively($parent, $methodName, $visibility);
    }
    private static function hasMethod(ReflectionClass $reflectionClass, string $methodName, Visibility $visibility) : bool
    {
        if (!$reflectionClass->hasMethod($methodName)) {
            return \false;
        }
        $method = $reflectionClass->getMethod($methodName);
        if ($visibility->isPublic()) {
            return $method->isPublic();
        }
        return $method->isProtected();
    }
}
