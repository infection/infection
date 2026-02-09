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

namespace Infection\Configuration;

use function array_map;
use function explode;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpStan;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use Infection\Configuration\SourceFilter\SourceFilter;
use Infection\Mutator\Mutator;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\TestFramework\TestFrameworkTypes;
use function ltrim;
use PhpParser\Node;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
readonly class Configuration
{
    private const LOG_VERBOSITY = [
        'all',
        'none',
        'default',
    ];

    /**
     * @param array<string, Mutator<Node>> $mutators
     * @param array<string, array<int, string>> $ignoreSourceCodeMutatorsMap
     * @param non-empty-string $configurationPathname
     */
    public function __construct(
        public float $processTimeout,
        public Source $source,
        public ?SourceFilter $sourceFilter,
        public Logs $logs,
        public string $logVerbosity,
        public string $tmpDir,
        public PhpUnit $phpUnit,
        public PhpStan $phpStan,
        public array $mutators,
        public string $testFramework,
        public ?string $bootstrap,
        public ?string $initialTestsPhpOptions,
        public string $testFrameworkExtraOptions,
        public ?string $staticAnalysisToolOptions,
        public string $coveragePath,
        public bool $skipCoverage,
        public bool $skipInitialTests,
        public bool $isDebugEnabled,
        private bool $withUncovered,
        public bool $noProgress,
        public bool $ignoreMsiWithNoMutations,
        public ?float $minMsi,
        public ?int $numberOfShownMutations,
        public ?float $minCoveredMsi,
        public bool $timeoutsAsEscaped,
        public ?int $maxTimeouts,
        public int $msiPrecision,
        public int $threadCount,
        public bool $isDryRun,
        public array $ignoreSourceCodeMutatorsMap,
        public bool $executeOnlyCoveringTestCases,
        public ?string $mapSourceClassToTestStrategy,
        public ?string $loggerProjectRootDirectory,
        public ?string $staticAnalysisTool,
        public ?string $mutantId,
        public string $configurationPathname,
    ) {
        Assert::nullOrGreaterThanEq($processTimeout, 0);
        Assert::allIsInstanceOf($mutators, Mutator::class);
        Assert::oneOf($logVerbosity, self::LOG_VERBOSITY);
        Assert::oneOf($testFramework, TestFrameworkTypes::getTypes());
        Assert::nullOrOneOf($staticAnalysisTool, StaticAnalysisToolTypes::getTypes());
        Assert::nullOrGreaterThanEq($minMsi, 0.);
        Assert::greaterThanEq($threadCount, 0);
    }

    public function isStaticAnalysisEnabled(): bool
    {
        return $this->staticAnalysisTool !== null;
    }

    /**
     * @return list<string>
     */
    public function getStaticAnalysisToolOptions(): array
    {
        if ($this->staticAnalysisToolOptions === null || $this->staticAnalysisToolOptions === '') {
            return [];
        }

        return $this->parseStaticAnalysisToolOptions($this->staticAnalysisToolOptions);
    }

    public function mutateOnlyCoveredCode(): bool
    {
        return !$this->withUncovered;
    }

    /**
     * @return list<string>
     */
    private function parseStaticAnalysisToolOptions(string $extraOptions): array
    {
        return array_map(
            static fn ($option): string => '--' . $option,
            explode(' --', ltrim($extraOptions, '-')),
        );
    }
}
