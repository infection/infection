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

namespace Infection\Tests\Architecture\PHPat\Selector\Support;

use function count;
use function is_string;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionAttribute;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function str_ends_with;
use function str_starts_with;

final class PHPUnitTestClassAnalysis
{
    private const string COVERS_ATTRIBUTE_NAMESPACE = 'PHPUnit\\Framework\\Attributes\\Covers';

    private const string GROUP_NAME = 'integration';

    public static function isPHPUnitTestCase(ClassReflection $classReflection): bool
    {
        return str_ends_with($classReflection->getName(), 'Test')
            && ConcreteClassReflection::isConcreteClass($classReflection)
            && $classReflection->isSubclassOf(TestCase::class);
    }

    public static function hasCoversNothing(ClassReflection $classReflection): bool
    {
        return count($classReflection->getNativeReflection()->getAttributes(CoversNothing::class)) > 0;
    }

    public static function belongsToIntegrationGroup(ClassReflection $classReflection): bool
    {
        foreach (self::getAttributes($classReflection) as $groupAttribute) {
            if (self::isIntegrationGroup($groupAttribute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<class-string>
     */
    public static function getCoveredClassNames(
        ClassReflection $testCaseReflection,
        ReflectionProvider $reflectionProvider,
    ): array {
        $coveredClassNames = [];

        foreach (self::getAttributes($testCaseReflection) as $attribute) {
            if (!self::isCoverageAttribute($attribute)) {
                continue;
            }

            $coveredClassName = self::getCoveredClassName($attribute, $reflectionProvider);

            if ($coveredClassName !== null) {
                $coveredClassNames[] = $coveredClassName;
            }
        }

        return $coveredClassNames;
    }

    /**
     * @see Group
     */
    private static function isIntegrationGroup(ReflectionAttribute $attribute): bool
    {
        if ($attribute->getName() !== Group::class) {
            return false;
        }

        $arguments = $attribute->getArguments();
        $groupName = $arguments[0] ?? $arguments['name'] ?? null;

        return $groupName === self::GROUP_NAME;
    }

    /**
     * TODO: eventually we should support functions, methods and traits too.
     * @see CoversClass
     *
     * @return class-string|null
     */
    private static function getCoveredClassName(
        ReflectionAttribute $attribute,
        ReflectionProvider $reflectionProvider,
    ): ?string {
        if ($attribute->getName() !== CoversClass::class) {
            return null;
        }

        $arguments = $attribute->getArguments();
        $coveredClassName = $arguments[0] ?? $arguments['className'] ?? null;

        $classNameExists = is_string($coveredClassName)
            && $reflectionProvider->hasClass($coveredClassName);

        return $classNameExists ? $coveredClassName : null;
    }

    private static function isCoverageAttribute(ReflectionAttribute $attribute): bool
    {
        return str_starts_with(
            $attribute->getName(),
            self::COVERS_ATTRIBUTE_NAMESPACE,
        );
    }

    /**
     * @return iterable<ReflectionAttribute>
     */
    private static function getAttributes(ClassReflection $classReflection): iterable
    {
        $attributes = $classReflection->getNativeReflection()->getAttributes();

        foreach ($attributes as $attribute) {
            if ($attribute instanceof ReflectionAttribute) {
                yield $attribute;
            }
        }
    }
}
