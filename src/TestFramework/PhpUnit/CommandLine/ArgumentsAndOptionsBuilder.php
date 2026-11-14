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
use function implode;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\TestFramework\Contracts\CommandLineArguments;
use SplFileInfo;
use function sprintf;
use Symfony\Component\Console\Input\InputOption;

/**
 * @internal
 */
final readonly class ArgumentsAndOptionsBuilder
{
    /**
     * @param SplFileInfo[] $filteredSourceFilesToMutate
     * @param list<InputOption> $initialRunOnlyOptions
     */
    private function __construct(
        private bool $executeOnlyCoveringTestCases,
        private array $filteredSourceFilesToMutate,
        private ?string $mapSourceClassToTestStrategy,
        private bool $useWindowsFilterLimit,
        private InputOption $filter,
        private array $initialRunOnlyOptions,
    ) {
    }

    /**
     * @param SplFileInfo[] $filteredSourceFilesToMutate
     */
    public static function create(
        bool $executeOnlyCoveringTestCases,
        array $filteredSourceFilesToMutate,
        ?string $mapSourceClassToTestStrategy,
        bool $useWindowsFilterLimit,
    ): self {
        $filter = new InputOption('filter', null, InputOption::VALUE_REQUIRED);

        return new self(
            $executeOnlyCoveringTestCases,
            $filteredSourceFilesToMutate,
            $mapSourceClassToTestStrategy,
            $useWindowsFilterLimit,
            $filter,
            [
                new InputOption('configuration', 'c', InputOption::VALUE_REQUIRED),
                $filter,
                new InputOption('testsuite', null, InputOption::VALUE_REQUIRED),
                new InputOption('warn-when-php-is-not-configured-for-development'),
            ],
        );
    }

    public function buildForInitialTestsRun(string $configPath, CommandLineArguments $arguments): CommandLineArguments
    {
        $arguments = $arguments->withOptionDefinitions(...$this->initialRunOnlyOptions);

        if ($this->filteredSourceFilesToMutate !== []
            && $this->mapSourceClassToTestStrategy !== null
            && !$arguments->hasOption($this->filter)
        ) {
            $filter = implode(
                '|',
                array_map(
                    $this->mapSourceClassToTestClass(...),
                    $this->filteredSourceFilesToMutate,
                ),
            );
            $arguments = $arguments->append(['--filter', $filter]);
        }

        return $arguments->prepend(['--configuration', $configPath]);
    }

    /**
     * @param TestLocation[] $tests
     */
    public function buildForMutant(string $configPath, CommandLineArguments $arguments, array $tests, string $testFrameworkVersion): CommandLineArguments
    {
        $arguments = $arguments
            ->withOptionDefinitions(...$this->initialRunOnlyOptions)
            ->withoutOptions(...$this->initialRunOnlyOptions)
        ;

        if ($this->executeOnlyCoveringTestCases && $tests !== []) {
            $filter = $this->createFilterString(
                $tests,
                $testFrameworkVersion,
            );

            if ($filter !== null) {
                $arguments = $arguments->append(['--filter', $filter]);
            }
        }

        return $arguments->prepend(['--configuration', $configPath]);
    }

    private function mapSourceClassToTestClass(SplFileInfo $sourceFile): string
    {
        return sprintf('%sTest', $sourceFile->getBasename('.' . $sourceFile->getExtension()));
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
        $filters = FilterBuilder::createFilters(
            $tests,
            $testFrameworkVersion,
            useWindowsFilterLimit: $this->useWindowsFilterLimit,
        );

        return $filters === []
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
