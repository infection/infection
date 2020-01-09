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
use Generator;
use Infection\Tests\AutoReview\ProjectCode\ProjectCodeProvider;
use Infection\Tests\AutoReview\SourceTestClassNameScheme;
use Infection\Tests\FileSystem\FileSystemTestCase;
use function iterator_to_array;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use function Safe\file_get_contents;
use function strpos;
use Webmozart\Assert\Assert;

final class IntegrationGroupProvider
{
    /**
     * @var string[][]|null
     */
    private static $ioTestCaseClassesTuple;

    private function __construct()
    {
    }

    /**
     * Note that the current implementation is far from being bullet-proof. For example as of now
     * it checks the source classes, but it is not excluded that a fixture file used in a test case
     * contains I/O operations. In this scenario, the current implementation would miss out that one.
     */
    public static function provideIoTestCaseTuple(): Generator
    {
        if (null !== self::$ioTestCaseClassesTuple) {
            yield from self::$ioTestCaseClassesTuple;

            return;
        }

        self::$ioTestCaseClassesTuple = array_values(array_filter(array_map(
            [self::class, 'checkIoOperations'],
            iterator_to_array(ProjectCodeProvider::provideSourceClasses(), true)
        )));

        yield from self::$ioTestCaseClassesTuple;
    }

    public static function ioTestCaseTupleProvider(): Generator
    {
        yield from self::provideIoTestCaseTuple();
    }

    /**
     * @return string[]|null
     */
    private static function checkIoOperations(string $className): ?array
    {
        $classReflection = new ReflectionClass($className);

        $testCaseClass = SourceTestClassNameScheme::getTestClassName($className);

        try {
            $testCaseReflection = new ReflectionClass($testCaseClass);
        } catch (ReflectionException $exception) {
            // No test case could be find for this source file
            return null;
        }

        //
        // Check the test cases code
        //
        $testCaseFileName = $testCaseReflection->getFileName();
        $testCaseCode = file_get_contents($testCaseFileName);

        // Case where the test case itself use I/O functions
        if (self::codeContainsIoFunctions($testCaseCode)) {
            return [$testCaseClass, $testCaseFileName];
        }

        $parentTestCaseClassReflection = $testCaseReflection->getParentClass();

        Assert::isInstanceOf($parentTestCaseClassReflection, ReflectionClass::class);

        while ($parentTestCaseClassReflection->getName() !== TestCase::class) {
            if ($parentTestCaseClassReflection->getName() === FileSystemTestCase::class) {
                return [$testCaseClass, $parentTestCaseClassReflection->getFileName()];
            }

            $parentTestCaseFileName = $parentTestCaseClassReflection->getFileName();

            $testCaseCode = file_get_contents($parentTestCaseFileName);

            if (self::codeContainsIoFunctions($testCaseCode)) {
                return [$testCaseClass, $parentTestCaseFileName];
            }

            $parentTestCaseClassReflection = $parentTestCaseClassReflection->getParentClass();

            Assert::isInstanceOf($parentTestCaseClassReflection, ReflectionClass::class);
        }

        //
        // Check the source class code
        //
        $classFileName = $classReflection->getFileName();
        $classCode = file_get_contents($classFileName);

        // Case where the test case itself use I/O functions
        if (self::codeContainsIoFunctions($classCode)) {
            return [$testCaseClass, $classFileName];
        }

        $parentClassReflection = $classReflection->getParentClass();

        while ($parentClassReflection !== false) {
            $parentClassFileName = $parentClassReflection->getFileName();

            if ($parentClassFileName === false) {
                break;
            }

            $parentClassCode = file_get_contents($parentClassFileName);

            if (self::codeContainsIoFunctions($parentClassCode)) {
                return [$testCaseClass, $parentClassFileName];
            }

            $parentClassReflection = $parentClassReflection->getParentClass();
        }

        return null;
    }

    private static function codeContainsIoFunctions(string $code): bool
    {
        if (strpos($code, 'file_get_contents') !== false) {
            return true;
        }

        if (strpos($code, 'file_put_contents') !== false) {
            return true;
        }

        if (strpos($code, 'file_exists') !== false) {
            return true;
        }

        if (strpos($code, 'fopen') !== false) {
            return true;
        }

        return false;
    }
}
