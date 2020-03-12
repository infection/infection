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

namespace Infection\Tests\AutoReview\EnvVariableManipulation;

use function array_filter;
use function array_map;
use function array_values;
use function class_exists;
use Infection\Tests\AutoReview\ProjectCode\ProjectCodeProvider;
use Infection\Tests\AutoReview\SourceTestClassNameScheme;
use function iterator_to_array;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use function Safe\file_get_contents;
use Webmozart\Assert\Assert;

final class EnvTestCasesProvider
{
    /**
     * @var string[][]|null
     */
    private static $envTestCaseClassesTuple;

    private function __construct()
    {
    }

    /**
     * Note that the current implementation is far from being bullet-proof. For example as of now
     * it checks the source classes, but it is not excluded that a fixture file used in a test case
     * contains env writings. In this scenario, the current implementation would miss out that one.
     */
    public static function provideEnvTestCaseTuple(): iterable
    {
        if (self::$envTestCaseClassesTuple !== null) {
            yield from self::$envTestCaseClassesTuple;

            return;
        }

        self::$envTestCaseClassesTuple = array_values(array_filter(array_map(
            static function (string $className): ?array {
                return self::envTestCaseTuple($className);
            },
            iterator_to_array(ProjectCodeProvider::provideSourceClasses(), true)
        )));

        yield from self::$envTestCaseClassesTuple;
    }

    public static function envTestCaseTupleProvider(): iterable
    {
        yield from self::provideEnvTestCaseTuple();
    }

    /**
     * @return ?array{0: string, 1: string}
     */
    private static function envTestCaseTuple(string $className): ?array
    {
        $testCaseClass = SourceTestClassNameScheme::getTestClassName($className);

        if (!class_exists($testCaseClass)) {
            // No test case could be find for this source file
            return null;
        }

        foreach ([
            self::checkTestCaseForEnvManipulations($testCaseClass),
            self::checkTestedClassForEnvManipulations($className),
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
    private static function checkTestCaseForEnvManipulations(string $testCaseClass): ?string
    {
        $testCaseReflection = new ReflectionClass($testCaseClass);
        Assert::isInstanceOf($testCaseReflection, ReflectionClass::class);

        $testCaseFileName = $testCaseReflection->getFileName();
        $testCaseCode = file_get_contents($testCaseFileName);

        if (EnvManipulatorCodeDetector::codeManipulatesEnvVariables($testCaseCode)) {
            return $testCaseFileName;
        }

        $parentTestCaseClassReflection = $testCaseReflection->getParentClass();

        Assert::isInstanceOf($parentTestCaseClassReflection, ReflectionClass::class);

        while ($parentTestCaseClassReflection->getName() !== TestCase::class) {
            $parentTestCaseFileName = $parentTestCaseClassReflection->getFileName();

            $testCaseCode = file_get_contents($parentTestCaseFileName);

            if (EnvManipulatorCodeDetector::codeManipulatesEnvVariables($testCaseCode)) {
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
    private static function checkTestedClassForEnvManipulations(string $className): ?string
    {
        $classReflection = new ReflectionClass($className);

        $classFileName = $classReflection->getFileName();
        $classCode = file_get_contents($classFileName);

        if (EnvManipulatorCodeDetector::codeManipulatesEnvVariables($classCode)) {
            return $classFileName;
        }

        $parentClassReflection = $classReflection->getParentClass();

        while ($parentClassReflection !== false) {
            $parentClassFileName = $parentClassReflection->getFileName();

            if ($parentClassFileName === false) {
                break;
            }

            $parentClassCode = file_get_contents($parentClassFileName);

            if (EnvManipulatorCodeDetector::codeManipulatesEnvVariables($parentClassCode)) {
                return $parentClassFileName;
            }

            $parentClassReflection = $parentClassReflection->getParentClass();
        }

        return null;
    }
}
