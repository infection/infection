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

namespace Infection\Process\Builder;

use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\MutationProcessWasFinished;
use Infection\Mutation\Mutation;
use Infection\Mutation\MutationExecutionResultFactory;
use Infection\Process\MutationProcess;
use Symfony\Component\Process\Process;
use function method_exists;

/**
 * @internal
 * @final
 */
class MutationProcessFactory
{
    private $testFrameworkAdapter;
    private $timeout;
    private $eventDispatcher;
    private $resultFactory;

    // TODO: is it necessary for the timeout to be an int?
    public function __construct(
        TestFrameworkAdapter $testFrameworkAdapter,
        int $timeout,
        EventDispatcher $eventDispatcher,
        MutationExecutionResultFactory $resultFactory
    ) {
        $this->testFrameworkAdapter = $testFrameworkAdapter;
        $this->timeout = $timeout;
        $this->eventDispatcher = $eventDispatcher;
        $this->resultFactory = $resultFactory;
    }

    public function createProcessForMutation(Mutation $mutation, string $testFrameworkExtraOptions = ''): MutationProcess
    {
        $process = new Process(
            $this->testFrameworkAdapter->getMutantCommandLine(
                $mutation->getTests(),
                $mutation->getFilePath(),
                $mutation->getHash(),
                $mutation->getOriginalFilePath(),
                $testFrameworkExtraOptions
            )
        );

        $process->setTimeout((float) $this->timeout);

        if (method_exists($process, 'inheritEnvironmentVariables')) {
            // in version 4.4.0 this method is deprecated and removed in 5.0.0
            $process->inheritEnvironmentVariables();
        }

        $mutationProcess = new MutationProcess($process, $mutation);

        $eventDispatcher = $this->eventDispatcher;
        $resultFactory = $this->resultFactory;

        $mutationProcess->registerTerminateProcessClosure(static function () use (
            $mutationProcess,
            $eventDispatcher,
            $resultFactory
        ): void {
            $eventDispatcher->dispatch(new MutationProcessWasFinished(
                $resultFactory->createFromProcess($mutationProcess))
            );
        });

        return $mutationProcess;
    }
}
