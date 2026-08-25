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

use function array_map;
use function array_slice;
use Closure;
use function implode;
use Infection\Mutant\MutantExecutionResult;
use Infection\TestFramework\Contracts\InitialTestsResult;
use Infection\TestFramework\Contracts\Mutant;
use Infection\TestFramework\Contracts\MutantEvaluationPipe;
use Infection\TestFramework\Contracts\TestFramework;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final readonly class CombinedTestFramework implements TestFramework
{
    /**
     * @param non-empty-list<TestFramework> $testFrameworks
     */
    public function __construct(
        private array $testFrameworks,
    ) {
        Assert::notEmpty($testFrameworks);
    }

    public function getName(): string
    {
        return implode(', ', array_map(
            static fn (TestFramework $testFramework): string => $testFramework->getName(),
            $this->testFrameworks,
        ));
    }

    public function getVersion(): string
    {
        return implode(', ', array_map(
            static fn (TestFramework $testFramework): string => $testFramework->getVersion(),
            $this->testFrameworks,
        ));
    }

    public function checkRequirements(): void
    {
        foreach ($this->testFrameworks as $testFramework) {
            $testFramework->checkRequirements();
        }
    }

    public function executeInitialRun(?Closure $onProgress = null): InitialTestsResult
    {
        foreach ($this->testFrameworks as $testFramework) {
            $testFramework->executeInitialRun($onProgress);
        }

        // If the individual results become relevant, they should be collected and exposed
        // explicitly instead of presenting one framework's result as the combined result.
        return new InitialTestsResult('');
    }

    public function test(Mutant $mutant): MutantExecutionResult|MutantEvaluationPipe
    {
        $pipes = [];

        foreach ($this->testFrameworks as $testFramework) {
            $result = $testFramework->test($mutant);

            if ($result instanceof MutantExecutionResult) {
                return $result;
            }

            $pipes[] = $result;
        }

        $pipe = $pipes[0];

        foreach (array_slice($pipes, 1) as $nextPipe) {
            $pipe = $pipe->merge($nextPipe);
        }

        return $pipe;
    }
}
