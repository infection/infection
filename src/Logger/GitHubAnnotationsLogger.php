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

namespace Infection\Logger;

use Infection\Metrics\ResultsCollector;
use function Safe\getcwd;
use function str_replace;
use Webmozart\PathUtil\Path;

/**
 * @internal
 */
final class GitHubAnnotationsLogger implements LineMutationTestingResultsLogger
{
    public const DEFAULT_OUTPUT = 'php://stdout';

    private ResultsCollector $resultsCollector;

    public function __construct(ResultsCollector $resultsCollector)
    {
        $this->resultsCollector = $resultsCollector;
    }

    public function getLogLines(): array
    {
        $lines = [];
        $currentWorkingDirectory = getcwd();

        foreach ($this->resultsCollector->getEscapedExecutionResults() as $escapedExecutionResult) {
            $error = [
                'line' => $escapedExecutionResult->getOriginalStartingLine(),
                'message' => <<<"TEXT"
Escaped Mutant for Mutator "{$escapedExecutionResult->getMutatorName()}":

{$escapedExecutionResult->getMutantDiff()}
TEXT
            ,
            ];

            $lines[] = $this->buildAnnotation(
                Path::makeRelative($escapedExecutionResult->getOriginalFilePath(), $currentWorkingDirectory),
                $error,
            );
        }

        return $lines;
    }

    /**
     * @param array{line: int, message: string} $error
     */
    private function buildAnnotation(string $filePath, array $error): string
    {
        // new lines need to be encoded
        // see https://github.com/actions/starter-workflows/issues/68#issuecomment-581479448
        $message = str_replace("\n", '%0A', $error['message']);

        return "::warning file={$filePath},line={$error['line']}::{$message}\n";
    }
}
