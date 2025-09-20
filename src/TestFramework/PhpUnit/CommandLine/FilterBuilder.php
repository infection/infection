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

namespace Infection\TestFramework\PhpUnit\CommandLine;

use function array_key_exists;
use function array_map;
use function array_merge;
use function count;
use function end;
use function explode;
use function implode;
use function in_array;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;
use function is_numeric;
use function ltrim;
use function preg_quote;
use function rtrim;
use SplFileInfo;
use function sprintf;
use function strlen;
use function version_compare;

/**
 * @internal
 */
final class FilterBuilder
{
    private const MAX_EXPLODE_PARTS = 2;

    // The real limit is likely higher, but it is better to be safe than sorry.
    private const PCRE_LIMIT = 30_000;

    /**
     * @param non-empty-array<TestLocation> $tests
     *
     * @return non-empty-array<string>
     */
    public static function createFilters(
        array $tests,
        string $testFrameworkVersion,
        int $optimizationLevel = 0,
    ): array
    {
        $usedTestCases = [];
        $filters = [];
        $totalFilterLength = 0;
        $attemptsCount = 0;

        if ($optimizationLevel === 1) {
            return [];
        }

        foreach ($tests as $testLocation) {
            $test = $testLocation->getMethod();
            $partsDelimitedByColons = explode('::', $test, self::MAX_EXPLODE_PARTS);

            if (count($partsDelimitedByColons) > 1) {
                [$testCaseClassName, $rawTestMethod] = $partsDelimitedByColons;

                $testMethod = self::getTestMethod($rawTestMethod, $testFrameworkVersion, $optimizationLevel);
                $testCaseShortClassName = self::getShortClassName($testCaseClassName);

                $test = sprintf(
                    '%s::%s',
                    $testCaseShortClassName,
                    $testMethod,
                );
            }

            if (array_key_exists($test, $usedTestCases)) {
                continue;
            }

            $usedTestCases[$test] = true;

            $filter = preg_quote($test, '/');
            $totalFilterLength += strlen($filter);

            if ($totalFilterLength > self::PCRE_LIMIT) {
                return self::createFilters(
                    $tests,
                    $testFrameworkVersion,
                    $optimizationLevel + 1,
                );
            }

            $filters[] = $filter;
        }

        return $filters;
    }

    /**
     * @param class-string $className
     *
     * @return string
     */
    private static function getShortClassName(string $className): string
    {
        $parts = explode('\\', $className);

        return end($parts);
    }

    private static function getTestMethod(
        string $methodNameWithDataProvider,
        string $testFrameworkVersion,
        int $optimizationLevel,
    ): string
    {
        if (1 === $optimizationLevel) {
            // Drop the data provider key when there is one.
            [$testMethod] = self::splitMethodNameFromProviderKey($methodNameWithDataProvider, $testFrameworkVersion);

            return $testMethod;
        }

        /*
         * in PHPUnit >=10 data providers with keys are stored as `Class\\test_method#some key` or `Class\\test_method#0`
         * in PHPUnit <10 data providers with keys are stored as `Class\\test_method with data set "some key"` or `Class\\test_method with data set #0`
         *
         * we need to translate to the old format because this is what PHPUnit <10 and >=10 understands from CLI `--filter` option
         */
        if (self::isPhpUnit10OrHigher($testFrameworkVersion)) {
            $methodNameParts = self::splitMethodNameFromProviderKey($methodNameWithDataProvider, $testFrameworkVersion);

            if (count($methodNameParts) > 1) {
                [$methodName, $dataProviderKey] = $methodNameParts;

                return is_numeric($dataProviderKey)
                    ? sprintf('%s with data set #%s', $methodName, $dataProviderKey)
                    : sprintf('%s with data set "%s"', $methodName, $dataProviderKey);
            }
        }

        return $methodNameWithDataProvider;
    }

    private static function splitMethodNameFromProviderKey(
        string $testMethod,
        string $testFrameworkVersion,
    ): array
    {
        return self::isPhpUnit10OrHigher($testFrameworkVersion)
            ? explode('#', $testMethod, self::MAX_EXPLODE_PARTS)
            : explode(' with data set ', $testMethod, self::MAX_EXPLODE_PARTS);
    }

    private static function isPhpUnit10OrHigher(string $testFrameworkVersion): bool
    {
        static $versions = [];

        if (!array_key_exists($testFrameworkVersion, $versions)) {
            $versions[$testFrameworkVersion] = version_compare($testFrameworkVersion, '10', '>=');
        }

        return $versions[$testFrameworkVersion];
    }
}
