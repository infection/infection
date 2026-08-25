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

use Closure;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestCaseWasCompleted;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasStarted;
use Infection\Mutant\MutantExecutionResult;
use Infection\TestFramework\Contracts\InitialTestsResult;
use Infection\TestFramework\Contracts\Mutant;
use Infection\TestFramework\Contracts\MutantEvaluationPipe;
use Infection\TestFramework\Contracts\TestFramework;
use InvalidArgumentException;

/** @internal */
final readonly class EventDispatchingTestFramework implements TestFramework
{
    public function __construct(
        private TestFramework $decorated,
        private EventDispatcher $eventDispatcher,
    ) {
    }

    public function getName(): string
    {
        return $this->decorated->getName();
    }

    public function getVersion(): string
    {
        return $this->decorated->getVersion();
    }

    public function checkRequirements(): void
    {
        $this->decorated->checkRequirements();
    }

    public function executeInitialRun(?Closure $onProgress = null): InitialTestsResult
    {
        $this->eventDispatcher->dispatch(new InitialTestSuiteWasStarted(
            $this->decorated->getName(),
            $this->retrieveVersion(),
        ));

        $result = $this->decorated->executeInitialRun(
            function () use ($onProgress): void {
                $onProgress?->__invoke();

                $this->eventDispatcher->dispatch(new InitialTestCaseWasCompleted());
            },
        );

        $this->eventDispatcher->dispatch(
            new InitialTestSuiteWasFinished($result->output),
        );

        return $result;
    }

    public function test(Mutant $mutant): MutantExecutionResult|MutantEvaluationPipe
    {
        return $this->decorated->test($mutant);
    }

    private function retrieveVersion(): string
    {
        try {
            return $this->decorated->getVersion();
        } catch (InvalidArgumentException) {
            return 'unknown';
        }
    }
}
