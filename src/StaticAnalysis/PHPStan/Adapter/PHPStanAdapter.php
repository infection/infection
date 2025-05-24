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

namespace Infection\StaticAnalysis\PHPStan\Adapter;

use Infection\StaticAnalysis\StaticAnalysisToolAdapter;
use Infection\TestFramework\CommandLineBuilder;

/**
 * @internal
 */
final class PHPStanAdapter implements StaticAnalysisToolAdapter
{
    public function __construct(
        private readonly string $staticAnalysisToolExecutable,
        private readonly CommandLineBuilder $commandLineBuilder,
    ) {
    }

    public function getName(): string
    {
        return 'PHPStan';
    }

    public function getInitialRunCommandLine(): array
    {
        // TODO add --stop-on-first-error. Talked to Ondrej - this is the only one way of stop
        // we can't rely on stderr because it's used for other output (non-error)
        // see https://github.com/phpstan/phpstan/issues/11352#issuecomment-2233403781

        // ../../../bin/infection --static-analysis-tool=phpstan -s --log-verbosity=all --debug -vvv
        // cat infection.text.log

        return $this->commandLineBuilder->build(
            $this->staticAnalysisToolExecutable,
            [],
            [],
            // todo ['--error-output=json', '--no-progress'],
            // todo ['--debug', '-vvv', '--no-ansi'],
        );
    }

    public function getMutantCommandLine(
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
                // TODO --stop-on-first-error
            ],
        );
    }
}
