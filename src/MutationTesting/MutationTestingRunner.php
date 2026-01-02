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

namespace Infection\MutationTesting;

use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Ast\AstCollector;
use Infection\Configuration\Configuration;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutation\MutationGenerator;
use Infection\MutationTesting\MutationAnalyzer\MutationAnalyzer;
use Infection\PhpParser\UnparsableFile;
use Infection\Process\Runner\MutationTestingRunner as LegacyMutationTestingRunner;
use Infection\Source\Collector\SourceCollector;
use Infection\Source\Exception\NoSourceFound;
use Infection\TestFramework\Coverage\JUnit\TestFileNameNotFoundException;
use Infection\TestFramework\Coverage\Locator\Throwable\NoReportFound;
use Infection\TestFramework\Coverage\Locator\Throwable\ReportLocationThrowable;
use Infection\TestFramework\Coverage\Locator\Throwable\TooManyReportsFound;
use Infection\TestFramework\Coverage\XmlReport\InvalidCoverage;
use Infection\TestFramework\ProvidesInitialRunOnlyOptions;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;
use function Pipeline\take;

/**
 * @internal
 */
final readonly class MutationTestingRunner
{
    public function __construct(
        private SourceCollector $sourceCollector,
        private AstCollector $astCollector,
        private MutationGenerator $mutationGenerator,
        // private MutationAnalyzer                $mutationAnalyzer,
        private LegacyMutationTestingRunner $legacyRunner,
        private Configuration $config,
        private TestFrameworkAdapter $adapter,
        private TestFrameworkExtraOptionsFilter $testFrameworkExtraOptionsFilter,
    ) {
    }

    /**
     * @throws UnparsableFile
     * @throws InvalidCoverage
     * @throws NoSourceFound
     * @throws NoReportFound
     * @throws TooManyReportsFound
     * @throws ReportLocationThrowable
     * @throws TestFileNameNotFoundException
     *
     * @return list<MutantExecutionResult>
     */
    public function runMutationAnalysis(): array
    {
        $mutations = take($this->sourceCollector->collect())
            ->map($this->astCollector->generate(...))
            ->filter(static fn ($ast) => $ast !== null)
            ->map($this->mutationGenerator->generate(...));
        // TODO: should use that instead of the legacy runner
        // ->map($this->mutationAnalyzer->analyze(...));

        $this->legacyRunner->run(
            $mutations,
            $this->getFilteredExtraOptionsForMutant(),
        );

        // TODO: currently we dispatch the events, we do not return the results
        return [];
    }

    private function getFilteredExtraOptionsForMutant(): string
    {
        if ($this->adapter instanceof ProvidesInitialRunOnlyOptions) {
            return $this->testFrameworkExtraOptionsFilter->filterForMutantProcess(
                $this->config->testFrameworkExtraOptions,
                $this->adapter->getInitialRunOnlyOptions(),
            );
        }

        return $this->config->testFrameworkExtraOptions;
    }
}
