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

namespace Infection\TestFramework;

use DomainException;
use Infection\Configuration\Configuration;
use Infection\Console\ConsoleOutput;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResult;
use Infection\Process\Runner\InitialStaticAnalysisRunFailed;
use Infection\Process\Runner\InitialStaticAnalysisRunner;
use Infection\StaticAnalysis\StaticAnalysisToolAdapter;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\MutantEvaluationPipe;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Coverage\CoverageChecker;
use Webmozart\Assert\Assert;

/**
 * @deprecated This is for the compatibility layer with the old AbstractTestFramework contract. To be removed.
 */
final readonly class LegacyStaticAnalysisBridge implements TestFramework
{
    public function __construct(
        private ?StaticAnalysisToolAdapter $adapter,
        private ?InitialStaticAnalysisRunner $initialStaticAnalysisRunner,
        private Configuration $config,
    ) {
    }

    public function getName(): string
    {
        return $this->adapter->getName();
    }

    public function getVersion(): string
    {
        return $this->adapter->getVersion();
    }

    public function checkRequirements(): void
    {
        if ($this->config->isStaticAnalysisEnabled()) {
            $this->adapter->assertMinimumVersionSatisfied();
        }
    }

    public function executeInitialRun(): InitialRunResults
    {
        Assert::notNull($this->initialStaticAnalysisRunner);
        Assert::notNull($this->adapter);

        //        if ($this->config->shouldSkipInitialTests()) {
        //            $this->consoleOutput->logSkippingInitialTests();
        //            $this->coverageChecker->checkCoverageExists();
        //
        //            return;
        //        }
        $initialStaticAnalysisProcess = $this->initialStaticAnalysisRunner->run();

        if (!$initialStaticAnalysisProcess->isSuccessful()) {
            throw InitialStaticAnalysisRunFailed::fromProcessAndAdapter(
                $initialStaticAnalysisProcess,
                $this->adapter->getName(),
            );
        }

        // todo [phpstan-integration] check cache has been generated
        //        $this->coverageChecker->checkCoverageHasBeenGenerated(
        //            $initialTestSuiteProcess->getCommandLine(),
        //            $initialTestSuiteProcess->getOutput(),
        //        );

        return new InitialRunResults(
            output: $initialStaticAnalysisProcess->getOutput(),
            memoryUsage: null,
        );
    }

    public function test(Mutant $mutant): MutantExecutionResult|MutantEvaluationPipe
    {
        // TODO: Implement test() method.
        //  at this point this is implemented directory via MutantProcessContainerFactory which
        //  uses the SA adapter to create the other factories.
        throw new DomainException('Not needed yet');
    }
}
