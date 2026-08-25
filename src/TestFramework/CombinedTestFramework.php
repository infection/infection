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

use BadMethodCallException;
use Closure;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResult;
use Infection\Process\MutantProcess;
use Infection\TestFramework\Contracts\InitialTestsResult;
use Infection\TestFramework\Contracts\MutantEvaluationPipe;
use Infection\TestFramework\Contracts\StaticAnalysisTestFramework;
use Infection\TestFramework\Contracts\TestFramework;

/**
 * TODO: Note that its current shape is temporary. It will later be more of a test framework registry.
 *
 * @internal
 */
final readonly class CombinedTestFramework implements TestFramework
{
    private const string UNSUPPORTED_OPERATION_MESSAGE = 'CombinedTestFramework only supports mutant evaluation.';

    public function __construct(
        private TestFramework $testFramework,
        private ?StaticAnalysisTestFramework $staticAnalysis,
    ) {
    }

    public function getName(): string
    {
        throw new BadMethodCallException(self::UNSUPPORTED_OPERATION_MESSAGE);
    }

    public function getVersion(): string
    {
        throw new BadMethodCallException(self::UNSUPPORTED_OPERATION_MESSAGE);
    }

    public function checkRequirements(): void
    {
        throw new BadMethodCallException(self::UNSUPPORTED_OPERATION_MESSAGE);
    }

    public function executeInitialRun(?Closure $onProgress = null): InitialTestsResult
    {
        throw new BadMethodCallException(self::UNSUPPORTED_OPERATION_MESSAGE);
    }

    public function test(Mutant $mutant): MutantExecutionResult|MutantEvaluationPipe
    {
        $testFrameworkResult = $this->testFramework->test($mutant);

        if ($testFrameworkResult instanceof MutantExecutionResult) {
            return $testFrameworkResult;
        }

        if ($this->staticAnalysis === null) {
            return $testFrameworkResult;
        }

        $staticAnalysisResult = $this->staticAnalysis->test($mutant);

        if ($staticAnalysisResult instanceof MutantExecutionResult) {
            return $staticAnalysisResult;
        }

        return $testFrameworkResult->append(
            static fn (): MutantProcess => $staticAnalysisResult->getCurrent(),
        );
    }
}
