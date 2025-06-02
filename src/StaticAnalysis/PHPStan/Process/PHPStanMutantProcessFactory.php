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

namespace Infection\StaticAnalysis\PHPStan\Process;

use Infection\Mutant\Mutant;
use Infection\Process\Factory\LazyMutantProcessFactory;
use Infection\Process\MutantProcess;
use Infection\StaticAnalysis\PHPStan\Mutant\PHPStanMutantExecutionResultFactory;
use Infection\TestFramework\CommandLineBuilder;
use Symfony\Component\Process\Process;

final class PHPStanMutantProcessFactory implements LazyMutantProcessFactory
{
    public function __construct(
        private PHPStanMutantExecutionResultFactory $mutantExecutionResultFactory,
        private readonly string $staticAnalysisToolExecutable,
        private readonly CommandLineBuilder $commandLineBuilder,
    ) {
    }

    public function create(Mutant $mutant): MutantProcess
    {
        $process = new Process(
            command: $this->getMutantCommandLine(
                $mutant->getFilePath(),
                $mutant->getMutation()->getOriginalFilePath(),
            ),
            timeout: 30, // todo [phpstan-integration] get from config
        );

        return new MutantProcess(
            $process,
            $mutant,
            $this->mutantExecutionResultFactory,
        );
    }

    private function getMutantCommandLine(
        string $mutatedFilePath,
        string $mutationOriginalFilePath,
    ): array {
        return $this->commandLineBuilder->build(
            $this->staticAnalysisToolExecutable,
            [],
            [
                "--tmp-file=$mutatedFilePath",
                "--instead-of=$mutationOriginalFilePath",
                '--error-format=json',
                '--no-progress',
                '-vv',
                // todo [phpstan-integration] --stop-on-first-error
            ],
        );
    }
}
