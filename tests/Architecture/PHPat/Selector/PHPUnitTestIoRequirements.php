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

use function class_exists;
use function count;
use Infection\FileSystem\FileSystem;
use Infection\Tests\AutoReview\IntegrationGroup\IoCodeDetector;
use function interface_exists;
use function is_string;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionAttribute;
use PHPStan\Reflection\ClassReflection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use function str_starts_with;
use function trait_exists;

final class PHPUnitTestIoRequirements
{
    private const string COVERS_ATTRIBUTE_NAMESPACE = 'PHPUnit\\Framework\\Attributes\\Covers';

    private const string GROUP_NAME = 'integration';

    /**
     * @var array<class-string, bool>
     */
    private array $classUsesIoCache = [];

    public function __construct(
        private readonly FileSystem $fileSystem,
    ) {
    }

    public function requiresIntegrationGroup(ClassReflection $testCaseReflection): bool
    {
        if ($this->testCaseUsesIo($testCaseReflection)) {
            return true;
        }

        $coveredClassNames = self::getCoveredClassNames($testCaseReflection);

        if ($coveredClassNames === null) {
            return true;
        }

        foreach ($coveredClassNames as $coveredClassName) {
            if ($this->testedClassUsesIo($coveredClassName)) {
                return true;
            }
        }

        return false;
    }

    public function hasCoveredClass(ClassReflection $testCaseReflection): bool
    {
        return self::getCoveredClassNames($testCaseReflection) !== null;
    }

    public function hasIntegrationGroup(ClassReflection $classReflection): bool
    {
        foreach (self::getAttributes($classReflection) as $groupAttribute) {
            if (self::isIntegrationGroup($groupAttribute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check the test case code.
     */
    private function testCaseUsesIo(ClassReflection $testCaseReflection): bool
    {
        if ($this->classUsesIo($testCaseReflection)) {
            return true;
        }

        $parentTestCaseClassReflection = $testCaseReflection->getParentClass();

        while (
            $parentTestCaseClassReflection !== null
            && $parentTestCaseClassReflection->getName() !== TestCase::class
        ) {
            if ($this->classUsesIo($parentTestCaseClassReflection)) {
                return true;
            }

            $parentTestCaseClassReflection = $parentTestCaseClassReflection->getParentClass();
        }

        return false;
    }

    /**
     * Check the source class code.
     *
     * @param class-string $sourceClassName
     */
    private function testedClassUsesIo(string $sourceClassName): bool
    {
        $classReflection = new ReflectionClass($sourceClassName);

        do {
            if ($this->classUsesIo($classReflection)) {
                return true;
            }

            $classReflection = $classReflection->getParentClass();
        } while ($classReflection !== false);

        return false;
    }

    /**
     * @return list<class-string>|null
     */
    private static function getCoveredClassNames(ClassReflection $testCaseReflection): ?array
    {
        $coveredClassNames = [];
        $hasCoverageAttribute = false;

        foreach (self::getAttributes($testCaseReflection) as $attribute) {
            if (!self::isCoverageAttribute($attribute)) {
                continue;
            }

            $hasCoverageAttribute = true;

            $coveredClassName = self::getCoveredClassName($attribute);

            if ($coveredClassName === null) {
                return null;
            }

            $coveredClassNames[] = $coveredClassName;
        }

        return $hasCoverageAttribute && count($coveredClassNames) > 0
            ? $coveredClassNames
            : null;
    }

    /**
     * TODO: eventually we should support functions, methods and traits too.
     * @see CoversClass
     *
     * @return class-string|null
     */
    private static function getCoveredClassName(ReflectionAttribute $attribute): ?string
    {
        if ($attribute->getName() !== CoversClass::class) {
            return null;
        }

        $arguments = $attribute->getArguments();
        $coveredClassName = $arguments[0] ?? $arguments['className'] ?? null;

        $classNameExists = !is_string($coveredClassName)
            || (
                !class_exists($coveredClassName)
                && !interface_exists($coveredClassName)
                && !trait_exists($coveredClassName)
            );

        return $classNameExists ? null : $coveredClassName;
    }

    private static function isCoverageAttribute(ReflectionAttribute $attribute): bool
    {
        return str_starts_with(
            $attribute->getName(),
            self::COVERS_ATTRIBUTE_NAMESPACE,
        );
    }

    /**
     * @param ReflectionClass<object>|ClassReflection $classReflection
     */
    private function classUsesIo(ReflectionClass|ClassReflection $classReflection): bool
    {
        $className = $classReflection->getName();

        if (isset($this->classUsesIoCache[$className])) {
            return $this->classUsesIoCache[$className];
        }

        $fileName = $classReflection->getFileName();

        return $this->classUsesIoCache[$className] = $fileName !== null
            && $fileName !== false
            && IoCodeDetector::codeContainsIoOperations($this->fileSystem->readFile($fileName));
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
