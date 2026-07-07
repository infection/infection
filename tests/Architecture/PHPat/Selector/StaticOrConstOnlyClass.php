<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\Architecture\PHPat\Selector;

use Infection\Tests\Architecture\PHPat\Selector\Support\ConcreteClassReflection;
use PHPat\Selector\SelectorInterface;
use PHPStan\Reflection\ClassReflection;
use ReflectionClass;
use ReflectionMethod;

final class StaticOrConstOnlyClass implements SelectorInterface
{
    public function getName(): string
    {
        return 'static or const-only class';
    }

    public function matches(ClassReflection $classReflection): bool
    {
        if (
            !ConcreteClassReflection::isConcreteClass($classReflection)
            || $classReflection->isEnum()
        ) {
            return false;
        }

        $nativeReflection = $classReflection->getNativeReflection();

        return self::hasStaticOrConstOnlyMembers($nativeReflection)
            && self::hasOnlyStaticProperties($nativeReflection)
            && self::hasOnlyStaticMethods($nativeReflection);
    }

    /**
     * @param ReflectionClass<object> $classReflection
     */
    private static function hasStaticOrConstOnlyMembers(ReflectionClass $classReflection): bool
    {
        return $classReflection->getConstants() !== []
            || $classReflection->getProperties() !== []
            || self::getNonConstructorMethods($classReflection) !== [];
    }

    /**
     * @param ReflectionClass<object> $classReflection
     */
    private static function hasOnlyStaticProperties(ReflectionClass $classReflection): bool
    {
        foreach ($classReflection->getProperties() as $property) {
            if (!$property->isStatic()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ReflectionClass<object> $classReflection
     */
    private static function hasOnlyStaticMethods(ReflectionClass $classReflection): bool
    {
        foreach (self::getNonConstructorMethods($classReflection) as $method) {
            if (!$method->isStatic()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ReflectionClass<object> $classReflection
     *
     * @return list<ReflectionMethod>
     */
    private static function getNonConstructorMethods(ReflectionClass $classReflection): array
    {
        $methods = [];

        foreach ($classReflection->getMethods() as $method) {
            if ($method->isConstructor()) {
                continue;
            }

            $methods[] = $method;
        }

        return $methods;
    }
}
