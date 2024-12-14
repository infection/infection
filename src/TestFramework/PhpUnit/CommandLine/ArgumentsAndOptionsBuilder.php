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
use function version_compare;

/**
 * @internal
 */
final readonly class ArgumentsAndOptionsBuilder implements CommandLineArgumentsAndOptionsBuilder
{
    private const MAX_EXPLODE_PARTS = 2;

    /**
     * @param list<SplFileInfo> $filteredSourceFilesToMutate
     */
    public function __construct(
        private bool $executeOnlyCoveringTestCases,
        private array $filteredSourceFilesToMutate,
        private ?string $mapSourceClassToTestStrategy,
    ) {
    }

    /**
     * @return list<string>
     */
    public function buildForInitialTestsRun(string $configPath, string $extraOptions): array
    {
        $options = $this->prepareArgumentsAndOptions($configPath, $extraOptions);

        if ($this->filteredSourceFilesToMutate !== []
            && $this->mapSourceClassToTestStrategy !== null
            && !in_array('--filter', $options, true)
        ) {
            $options[] = '--filter';

            $options[] = implode(
                '|',
                array_map(
                    $this->mapSourceClassToTestClass(...),
                    $this->filteredSourceFilesToMutate,
                ),
            );
        }

        return $options;
    }

    /**
     * @param TestLocation[] $tests
     * @return list<string>
     */
    public function buildForMutant(string $configPath, string $extraOptions, array $tests, string $testFrameworkVersion): array
    {
        $options = $this->prepareArgumentsAndOptions($configPath, $extraOptions);

        if ($this->executeOnlyCoveringTestCases && count($tests) > 0) {
            $filterString = '/';
            $usedTestCases = [];

            foreach ($tests as $testLocation) {
                $testCaseString = $testLocation->getMethod();

                $partsDelimitedByColons = explode('::', $testCaseString, self::MAX_EXPLODE_PARTS);

                if (count($partsDelimitedByColons) > 1) {
                    $methodNameWithDataProvider = $this->getMethodNameWithDataProvider($partsDelimitedByColons[1], $testFrameworkVersion);

                    $testClassFullyQualifiedClassName = $partsDelimitedByColons[0];

                    $parts = explode('\\', $testClassFullyQualifiedClassName);
                    $classNameWithoutNamespace = end($parts);

                    $testCaseString = sprintf('%s::%s', $classNameWithoutNamespace, $methodNameWithDataProvider);
                }

                if (array_key_exists($testCaseString, $usedTestCases)) {
                    continue;
                }

                $usedTestCases[$testCaseString] = true;

                $filterString .= preg_quote($testCaseString, '/') . '|';
            }

            $filterString = rtrim($filterString, '|') . '/';

            $options[] = '--filter';
            $options[] = $filterString;
        }

        return $options;
    }

    private function mapSourceClassToTestClass(SplFileInfo $sourceFile): string
    {
        return sprintf('%sTest', $sourceFile->getBasename('.' . $sourceFile->getExtension()));
    }

    /**
     * @return list<string>
     */
    private function prepareArgumentsAndOptions(string $configPath, string $extraOptions): array
    {
        $options = [
            '--configuration',
            $configPath,
        ];

        if ($extraOptions !== '') {
            $options = array_merge(
                $options,
                array_map(
                    static fn ($option): string => '--' . $option,
                    explode(' --', ltrim($extraOptions, '-')),
                ),
            );
        }

        return $options;
    }

    private function getMethodNameWithDataProvider(string $methodNameWithDataProvider, string $testFrameworkVersion): string
    {
        $methodNameWithDataProviderResult = $methodNameWithDataProvider;

        /*
         * in PHPUnit >=10 data providers with keys are stored as `Class\\test_method#some key` or `Class\\test_method#0`
         * in PHPUnit <10 data providers with keys are stored as `Class\\test_method with data set "some key"` or `Class\\test_method with data set #0`
         *
         * we need to translate to the old format because this is what PHPUnit <10 and >=10 understands from CLI `--filter` option
         */
        if (version_compare($testFrameworkVersion, '10', '>=')) {
            $methodNameParts = explode('#', $methodNameWithDataProviderResult, self::MAX_EXPLODE_PARTS);

            if (count($methodNameParts) > 1) {
                [$methodName, $dataProviderKey] = $methodNameParts;

                if (!is_numeric($dataProviderKey)) {
                    $methodNameWithDataProviderResult = sprintf('%s with data set "%s"', $methodName, $dataProviderKey);
                } else {
                    $methodNameWithDataProviderResult = sprintf('%s with data set #%s', $methodName, $dataProviderKey);
                }
            }
        }

        return $methodNameWithDataProviderResult;
    }
}
