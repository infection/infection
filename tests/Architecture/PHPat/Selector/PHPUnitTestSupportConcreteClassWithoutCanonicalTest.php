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

use function in_array;
use Infection\Framework\ClassName;
use PHPat\Selector\SelectorInterface;
use PHPStan\Reflection\ClassReflection;
use PHPUnit\Framework\TestCase;
use function str_ends_with;
use Symfony\Component\Filesystem\Path;

final class PHPUnitTestSupportConcreteClassWithoutCanonicalTest implements SelectorInterface
{
    private const PHPUNIT_TEST_FIXTURE_NAMESPACES = [
        'Infection\Tests\Architecture\PHPat\Selector\HasDocBlock',
        'Infection\Tests\Architecture\PHPat\Selector\HasInternalDocBlock',
        'Infection\Tests\Framework\Enum\EnumBucket',
        'Infection\Tests\Framework\Enum\ImplodableEnum',
    ];

    private const PHPUNIT_TEST_FIXTURE_CLASSES = [
        'Infection\Tests\FileSystem\Finder\MockVendor',
        'Infection\Tests\Framework\Iterable\GeneratorFactory\SimpleIteratorAggregate',
        'Infection\Tests\Reflection\ProtChild',
        'Infection\Tests\Reflection\ProtParent',
    ];

    private const PHPUNIT_DATA_PROVIDER_NAMESPACES = [
        'Infection\Tests\TestFramework\Coverage\JUnit\JUnitTestFileDataProvider',
        'Infection\Tests\TestFramework\Coverage\XmlReport\SourceFileInfoProvider',
        'Infection\Tests\TestFramework\Coverage\XmlReport\XmlCoverageParser',
        'Infection\Tests\TestFramework\Tracing\Tracer',
    ];

    private const PHPUNIT_DATA_PROVIDER_CLASSES = [
        'Infection\Tests\Mutator\MutatorFixturesProvider',
    ];

    private const string PROJECT_ROOT = __DIR__ . '/../../../../';

    public function getName(): string
    {
        return 'PHPUnit test support concrete class without canonical test';
    }

    public function matches(ClassReflection $classReflection): bool
    {
        if (
            !self::isConcreteClass($classReflection)
            || InfectionSelector::isAnonymousClass()->matches($classReflection)
            || self::isPHPUnitTestClass($classReflection)
            || self::isKnownPhpUnitDataProviderClass($classReflection)
            || self::isKnownPhpUnitScenarioClass($classReflection)
            || self::isKnownPHPUnitTestFixtureClass($classReflection)
            || !self::isPHPUnitTestSupportCode($classReflection)
        ) {
            return false;
        }

        $className = $classReflection->getName();

        return ClassName::getCanonicalTestClassName($className) === null;
    }

    private static function isConcreteClass(ClassReflection $classReflection): bool
    {
        return !$classReflection->isAbstract()
            && !$classReflection->isInterface()
            && !$classReflection->isTrait();
    }

    private static function isPHPUnitTestSupportCode(ClassReflection $classReflection): bool
    {
        $fileName = $classReflection->getFileName();

        return $fileName !== null
            && Path::isBasePath(
                'tests/phpunit',
                Path::makeRelative($fileName, self::PROJECT_ROOT),
            );
    }

    private static function isPHPUnitTestClass(ClassReflection $classReflection): bool
    {
        $testCaseClassReflection = self::findParentClassReflection($classReflection, TestCase::class);

        return str_ends_with($classReflection->getName(), 'Test')
            && $testCaseClassReflection !== null
            && $classReflection->isSubclassOfClass($testCaseClassReflection);
    }

    /**
     * @param class-string $parentClassName
     */
    private static function findParentClassReflection(
        ClassReflection $classReflection,
        string $parentClassName,
    ): ?ClassReflection {
        $parentClassReflection = $classReflection->getParentClass();

        while ($parentClassReflection !== null) {
            if ($parentClassReflection->getName() === $parentClassName) {
                return $parentClassReflection;
            }

            $parentClassReflection = $parentClassReflection->getParentClass();
        }

        return null;
    }

    private static function isKnownPhpUnitDataProviderClass(ClassReflection $classReflection): bool
    {
        $className = $classReflection->getName();

        return in_array($className, self::PHPUNIT_DATA_PROVIDER_CLASSES, true)
            || in_array(
                ClassName::getNamespace($className),
                self::PHPUNIT_DATA_PROVIDER_NAMESPACES,
                true,
            );
    }

    private static function isKnownPhpUnitScenarioClass(ClassReflection $classReflection): bool
    {
        return str_ends_with($classReflection->getName(), 'Scenario');
    }

    private static function isKnownPHPUnitTestFixtureClass(ClassReflection $classReflection): bool
    {
        $className = $classReflection->getName();

        return in_array($className, self::PHPUNIT_TEST_FIXTURE_CLASSES, true)
            || in_array(
                ClassName::getNamespace($className),
                self::PHPUNIT_TEST_FIXTURE_NAMESPACES,
                true,
            );
    }
}
