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

namespace Infection\Tests\AutoReview\IntegrationGroup;

use function array_filter;
use function array_map;
use function array_values;
use function class_exists;
use Infection\CannotBeInstantiated;
use Infection\Tests\AutoReview\ProjectCode\ProjectCodeProvider;
use Infection\Tests\AutoReview\SourceTestClassNameScheme;
use Infection\Tests\Console\E2ETest;
use Infection\Tests\FileSystem\FileSystemTestCase;
use function iterator_to_array;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use function Safe\file_get_contents;
use Webmozart\Assert\Assert;

final class IntegrationGroupProvider
{
    use CannotBeInstantiated;

    /**
     * List of known integrational tests that must be treated as such.
     *
     * A better solution would be to find all tests that do not have corresponding class.
     */
    private const KNOWN_INTEGRATIONAL_TESTS = [
        E2ETest::class,
    ];

    /**
     * @var string[][]|null
     */
    private static $ioTestCaseClassesTuple;

    /**
     * Note that the current implementation is far from being bullet-proof. For example as of now
     * it checks the source classes, but it is not excluded that a fixture file used in a test case
     * contains I/O operations. In this scenario, the current implementation would miss out that one.
     */
    public static function provideIoTestCaseTuple(): iterable
    {
        if (self::$ioTestCaseClassesTuple !== null) {
            yield from self::$ioTestCaseClassesTuple;

            return;
        }

        self::$ioTestCaseClassesTuple = array_values(array_filter(array_map(
            static function (string $className): ?array {
                return self::ioTestCaseTuple($className);
            },
            iterator_to_array(ProjectCodeProvider::provideSourceClasses(), true)
        )));

        foreach (self::KNOWN_INTEGRATIONAL_TESTS as $testCaseClass) {
            $testCaseReflection = new ReflectionClass($testCaseClass);
            Assert::isInstanceOf($testCaseReflection, ReflectionClass::class);

            self::$ioTestCaseClassesTuple[] = [
                $testCaseClass,
                $testCaseReflection->getFileName(),
            ];
        }

        yield from self::$ioTestCaseClassesTuple;
    }

    public static function ioTestCaseTupleProvider(): iterable
    {
        yield from self::provideIoTestCaseTuple();
    }

    /**
     * @return ?array{0: string, 1: string}
     */
    private static function ioTestCaseTuple(string $className): ?array
    {
        $testCaseClass = SourceTestClassNameScheme::getTestClassName($className);

        if (!class_exists($testCaseClass)) {
            // No test case could be find for this source file
            return null;
        }

        foreach ([
            self::checkTestCaseForIoOperations($testCaseClass),
            self::checkTestedClassForIoOperations($className),
        ] as $classFileNameWithIoOperations) {
            if ($classFileNameWithIoOperations === null) {
                continue;
            }

            return [
                $testCaseClass,
                $classFileNameWithIoOperations,
            ];
        }

        return null;
    }

    /**
     * Check the test cases code.
     */
    private static function checkTestCaseForIoOperations(string $testCaseClass): ?string
    {
        $testCaseReflection = new ReflectionClass($testCaseClass);
        Assert::isInstanceOf($testCaseReflection, ReflectionClass::class);

        $testCaseFileName = $testCaseReflection->getFileName();
        $testCaseCode = file_get_contents($testCaseFileName);

        if (IoCodeDetector::codeContainsIoOperations($testCaseCode)) {
            return $testCaseFileName;
        }

        $parentTestCaseClassReflection = $testCaseReflection->getParentClass();

        Assert::isInstanceOf($parentTestCaseClassReflection, ReflectionClass::class);

        while ($parentTestCaseClassReflection->getName() !== TestCase::class) {
            if ($parentTestCaseClassReflection->getName() === FileSystemTestCase::class) {
                return $parentTestCaseClassReflection->getFileName();
            }

            $parentTestCaseFileName = $parentTestCaseClassReflection->getFileName();

            $testCaseCode = file_get_contents($parentTestCaseFileName);

            if (IoCodeDetector::codeContainsIoOperations($testCaseCode)) {
                return $parentTestCaseFileName;
            }

            $parentTestCaseClassReflection = $parentTestCaseClassReflection->getParentClass();

            Assert::isInstanceOf($parentTestCaseClassReflection, ReflectionClass::class);
        }

        return null;
    }

    /**
     * Check the source class code.
     */
    private static function checkTestedClassForIoOperations(string $className): ?string
    {
        $classReflection = new ReflectionClass($className);

        $classFileName = $classReflection->getFileName();
        $classCode = file_get_contents($classFileName);

        if (IoCodeDetector::codeContainsIoOperations($classCode)) {
            return $classFileName;
        }

        $parentClassReflection = $classReflection->getParentClass();

        while ($parentClassReflection !== false) {
            $parentClassFileName = $parentClassReflection->getFileName();

            if ($parentClassFileName === false) {
                break;
            }

            $parentClassCode = file_get_contents($parentClassFileName);

            if (IoCodeDetector::codeContainsIoOperations($parentClassCode)) {
                return $parentClassFileName;
            }

            $parentClassReflection = $parentClassReflection->getParentClass();
        }

        return null;
    }
}
