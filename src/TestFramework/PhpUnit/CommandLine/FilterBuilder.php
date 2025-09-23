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
use function count;
use function end;
use function explode;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use function is_numeric;
use function preg_quote;
use function sprintf;
use function version_compare;

/**
 * @internal
 */
final class FilterBuilder
{
    private const MAX_EXPLODE_PARTS = 2;

    /**
     * @param non-empty-array<TestLocation> $tests
     *
     * @return list<string>
     */
    public static function createFilters(
        array $tests,
        string $testFrameworkVersion,
    ): array {
        $usedTests = [];
        $filters = [];

        foreach ($tests as $testLocation) {
            $test = $testLocation->getMethod();
            $partsDelimitedByColons = explode('::', $test, self::MAX_EXPLODE_PARTS);

            if (count($partsDelimitedByColons) > 1) {
                [$testCaseClassName, $rawTestMethod] = $partsDelimitedByColons;

                $testMethodWithProviderKey = self::getTestMethodWithProviderKey($rawTestMethod, $testFrameworkVersion);
                $shortClassName = self::getShortClassName($testCaseClassName);

                $test = sprintf(
                    '%s::%s',
                    $shortClassName,
                    $testMethodWithProviderKey,
                );
            }

            if (array_key_exists($test, $usedTests)) {
                continue;
            }

            $usedTests[$test] = true;

            $filter = preg_quote($test, '/');
            $filters[] = $filter;
        }

        return $filters;
    }

    private static function getTestMethodWithProviderKey(
        string $methodNameWithDataProvider,
        string $testFrameworkVersion,
    ): string {
        /*
         * in PHPUnit >=10 data providers with keys are stored as `Class\\test_method#some key` or `Class\\test_method#0`
         * in PHPUnit <10 data providers with keys are stored as `Class\\test_method with data set "some key"` or `Class\\test_method with data set #0`
         *
         * we need to translate to the old format because this is what PHPUnit <10 and >=10 understands from CLI `--filter` option
         */
        if (self::isPhpUnit10OrHigher($testFrameworkVersion)) {
            $methodNameParts = self::splitMethodNameFromProviderKey($methodNameWithDataProvider);

            if (count($methodNameParts) > 1) {
                [$methodName, $dataProviderKey] = $methodNameParts;

                return is_numeric($dataProviderKey)
                    ? sprintf('%s with data set #%s', $methodName, $dataProviderKey)
                    : sprintf('%s with data set "%s"', $methodName, $dataProviderKey);
            }
        }

        return $methodNameWithDataProvider;
    }

    private static function getShortClassName(string $className): string
    {
        $parts = explode('\\', $className);

        return end($parts);
    }

    /**
     * @return array{string, string}
     */
    private static function splitMethodNameFromProviderKey(string $testMethod): array
    {
        // @phpstan-ignore return.type
        return explode('#', $testMethod, self::MAX_EXPLODE_PARTS);
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
