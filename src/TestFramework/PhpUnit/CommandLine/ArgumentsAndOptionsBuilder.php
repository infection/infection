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

use function array_map;
use function array_merge;
use function count;
use function explode;
use function implode;
use function in_array;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;
use function ltrim;
use SplFileInfo;
use function sprintf;

/**
 * @internal
 */
final readonly class ArgumentsAndOptionsBuilder implements CommandLineArgumentsAndOptionsBuilder
{
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
            $filter = $this->createFilterString(
                $tests,
                $testFrameworkVersion,
            );

            if ($filter !== null) {
                $options[] = '--filter';
                $options[] = $filter;
            }
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

    /**
     * @param non-empty-array<TestLocation> $tests
     *
     * @return non-empty-string
     */
    private function createFilterString(
        array $tests,
        string $testFrameworkVersion,
    ): ?string {
        $filters = FilterBuilder::createFilters($tests, $testFrameworkVersion);

        return count($filters) === 0
            ? null
            : sprintf(
                '/%s/',
                implode(
                    '|',
                    $filters,
                ),
            );
    }
}
