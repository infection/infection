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
use function explode;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\CannotBeInstantiated;
use Infection\Framework\ClassName;
use function preg_quote;
use function sprintf;
use function strlen;
use function version_compare;

/**
 * @internal
 */
final class FilterBuilder
{
    use CannotBeInstantiated;

    private const int MAX_EXPLODE_PARTS = 2;

    // The real limit is likely higher, but it is better to be safe than sorry.
    private const int PCRE_LIMIT = 30_000;

    /*
     * Symfony may transport command arguments through the environment on Windows, where the
     * complete environment block is limited to 32,767 UTF-16 code units. This conservative
     * limit reserves headroom for inherited variables, may intentionally degrade filters
     * earlier, and is not an exact guarantee.
     */
    private const int WINDOWS_FILTER_LIMIT = 10_000;

    private const int NO_OPTIMIZATION_LEVEL = 0;

    private const int DROP_DATA_PROVIDER_KEY_OPTIMIZATION_LEVEL = 1;

    private const int DROP_TEST_CASE_OPTIMIZATION_LEVEL = 2;

    private const int BAILOUT_OPTIMIZATION_LEVEL = 3;

    /**
     * @param non-empty-array<TestLocation> $tests
     * @param self::NO_OPTIMIZATION_LEVEL|self::DROP_DATA_PROVIDER_KEY_OPTIMIZATION_LEVEL|self::DROP_TEST_CASE_OPTIMIZATION_LEVEL|self::BAILOUT_OPTIMIZATION_LEVEL $optimizationLevel
     *
     * @return list<string>
     */
    public static function createFilters(
        array $tests,
        string $testFrameworkVersion,
        bool $useWindowsFilterLimit = false,
        int $optimizationLevel = self::NO_OPTIMIZATION_LEVEL,
    ): array {
        $usedTests = [];
        $filters = [];
        $totalFilterLength = 0;
        $maxFilterLength = $useWindowsFilterLimit
            ? self::WINDOWS_FILTER_LIMIT
            : self::PCRE_LIMIT;

        if ($optimizationLevel === self::BAILOUT_OPTIMIZATION_LEVEL) {
            // We have no further optimisation strategy at this point, so we
            // simply give up and do not apply any filter.
            return [];
        }

        foreach ($tests as $testLocation) {
            $test = $testLocation->getMethod();
            $partsDelimitedByColons = explode('::', $test, self::MAX_EXPLODE_PARTS);

            if (count($partsDelimitedByColons) > 1) {
                [$testCaseClassName, $rawTestMethod] = $partsDelimitedByColons;

                // This may or not have the provider key.
                $testMethod = self::getMethod($rawTestMethod, $testFrameworkVersion, $optimizationLevel);
                $shortClassName = ClassName::getShortClassName($testCaseClassName);

                $test = $optimizationLevel >= self::DROP_TEST_CASE_OPTIMIZATION_LEVEL
                    ? $testMethod
                    : sprintf(
                        '%s::%s',
                        $shortClassName,
                        $testMethod,
                    );
            }

            if (array_key_exists($test, $usedTests)) {
                continue;
            }

            $usedTests[$test] = true;

            $filter = preg_quote($test, '/');

            $totalFilterLength += strlen($filter);

            if ($totalFilterLength > $maxFilterLength) {
                return self::createFilters(
                    $tests,
                    $testFrameworkVersion,
                    $useWindowsFilterLimit,
                    $optimizationLevel + 1,
                );
            }

            $filters[] = $filter;
        }

        return $filters;
    }

    /**
     * @param string $rawTestMethod Either the method name or the method name with data provider
     *                              key
     *
     * @return string A normalized form of the method name with the data provider key or the method
     *                name alone depending on the optimization level
     */
    private static function getMethod(
        string $rawTestMethod,
        string $testFrameworkVersion,
        int $optimizationLevel,
    ): string {
        if ($optimizationLevel >= self::DROP_DATA_PROVIDER_KEY_OPTIMIZATION_LEVEL) {
            // Drop the data provider key when there is one.
            [$testMethod] = self::splitMethodNameFromProviderKey($rawTestMethod, $testFrameworkVersion);

            return $testMethod;
        }

        /*
         * in PHPUnit >=10 data providers with keys are stored as `Class\\test_method#some key` or `Class\\test_method#0`
         * in PHPUnit <10 data providers with keys are stored as `Class\\test_method with data set "some key"` or `Class\\test_method with data set #0`
         *
         * Translate coverage IDs to the test names that PHPUnit matches with `--filter`.
         */
        if (self::isPhpUnit10OrHigher($testFrameworkVersion)) {
            $methodNameParts = self::splitMethodNameFromProviderKey($rawTestMethod, $testFrameworkVersion);

            if (count($methodNameParts) > 1) {
                [$methodName, $dataProviderKey] = $methodNameParts;

                return self::formatMethodWithDataSet(
                    $methodName,
                    $dataProviderKey,
                    $testFrameworkVersion,
                );
            }
        }

        return $rawTestMethod;
    }

    private static function formatMethodWithDataSet(
        string $methodName,
        string $dataProviderKey,
        string $testFrameworkVersion,
    ): string {
        if (self::isIntegerKey($dataProviderKey)) {
            return sprintf(
                '%s with data set #%s',
                $methodName,
                $dataProviderKey,
            );
        }

        // PHPUnit 12.3 added an @ prefix to named data sets. 12.4.1 restored the
        // previous filter format: https://github.com/sebastianbergmann/phpunit/pull/6364
        if (self::isPhpUnit123OrHigher($testFrameworkVersion)
            && !self::isPhpUnit1241OrHigher($testFrameworkVersion)
        ) {
            $dataProviderKey = '@' . $dataProviderKey;
        }

        return sprintf(
            '%s with data set "%s"',
            $methodName,
            $dataProviderKey,
        );
    }

    /**
     * PHPUnit stores data sets in an array: "1" becomes an int key, "01" stays a string.
     */
    private static function isIntegerKey(string $dataProviderKey): bool
    {
        return $dataProviderKey === (string) (int) $dataProviderKey;
    }

    /**
     * @return array{string, string}
     */
    private static function splitMethodNameFromProviderKey(
        string $testMethod,
        string $testFrameworkVersion,
    ): array {
        // @phpstan-ignore return.type
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

    private static function isPhpUnit123OrHigher(string $testFrameworkVersion): bool
    {
        static $versions = [];

        if (!array_key_exists($testFrameworkVersion, $versions)) {
            $versions[$testFrameworkVersion] = version_compare($testFrameworkVersion, '12.3.0', '>=');
        }

        return $versions[$testFrameworkVersion];
    }

    private static function isPhpUnit1241OrHigher(string $testFrameworkVersion): bool
    {
        static $versions = [];

        if (!array_key_exists($testFrameworkVersion, $versions)) {
            $versions[$testFrameworkVersion] = version_compare($testFrameworkVersion, '12.4.1', '>=');
        }

        return $versions[$testFrameworkVersion];
    }
}
