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

namespace Infection\Testing\TestFramework\Debug;

use Infection\Mutant\Mutant;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessContainer;
use Infection\TestFramework\Common\InitialTestsRunner;
use Infection\TestFramework\Common\Throwable\InitialTestsFailed;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\StaticAnalysisTestFramework;
use Override;
use Symfony\Component\Process\Exception\RuntimeException;

/**
 * @internal
 */
final readonly class DebugStaticAnalysisAdapter implements StaticAnalysisTestFramework
{
    public function __construct(
        private string $runtime,
        private string $logFile,
        private float $timeout,
        private DebugCommandLine $commandLine,
        private InitialTestsRunner $initialTestsRunner,
    ) {
    }

    #[Override]
    public function getName(): string
    {
        return 'Debug';
    }

    /**
     * @throws InitialTestsFailed
     * @throws RuntimeException When the initial command cannot complete.
     */
    #[Override]
    public function executeInitialRun(): InitialRunResults
    {
        $process = $this->initialTestsRunner->run(
            $this,
            $this->getInitialRunCommandLine(),
            testFrameworkType: 'static analysis',
        );

        return new InitialRunResults($process->stdout, null);
    }

    #[Override]
    public function test(Mutant $mutant): MutantProcessContainer
    {
        $factory = new DebugStaticAnalysisMutantProcessFactory(
            $this->runtime,
            $this->logFile,
            $this->timeout,
            $this->commandLine,
        );

        return MutantProcessContainer::from(
            $mutant,
            static fn (): MutantProcess => $factory->create($mutant),
        );
    }

    #[Override]
    public function hasJUnitReport(): bool
    {
        return false;
    }

    #[Override]
    public function getVersion(): string
    {
        return '1.0.0';
    }

    #[Override]
    public function checkRequirements(): void
    {
        // Nothing to do.
    }

    /**
     * @return string[]
     */
    private function getInitialRunCommandLine(): array
    {
        return $this->commandLine->create(
            runtime: $this->runtime,
            phpArguments: [],
            options: [
                'stage' => 'static-analysis-initial',
                'log' => $this->logFile,
            ],
        );
    }
}
