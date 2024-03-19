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

namespace Infection\Process\Runner;

use Exception;
use function implode;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use function sprintf;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class InitialTestsFailed extends Exception
{
    public static function fromProcessAndAdapter(
        Process $initialTestSuiteProcess,
        TestFrameworkAdapter $testFrameworkAdapter,
    ): self {
        $testFrameworkKey = $testFrameworkAdapter->getName();

        $lines = [
            'Project tests must be in a passing state before running Infection.',
            $testFrameworkAdapter->getInitialTestsFailRecommendations($initialTestSuiteProcess->getCommandLine()),
            sprintf(
                '%s reported an exit code of %d.',
                $testFrameworkKey,
                $initialTestSuiteProcess->getExitCode(),
            ),
            sprintf(
                'Refer to the %s\'s output below:',
                $testFrameworkKey,
            ),
        ];

        $stdOut = $initialTestSuiteProcess->getOutput();

        if ($stdOut !== '') {
            $lines[] = 'STDOUT:';
            $lines[] = $stdOut;
        }

        $stdError = $initialTestSuiteProcess->getErrorOutput();

        if ($stdError !== '') {
            $lines[] = 'STDERR:';
            $lines[] = $stdError;
        }

        return new self(implode("\n", $lines));
    }
}
