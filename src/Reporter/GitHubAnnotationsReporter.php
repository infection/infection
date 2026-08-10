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

namespace Infection\Reporter;

use function array_map;
use Infection\Metrics\ResultsCollector;
use Infection\Mutant\MutantExecutionResult;
use function sprintf;
use function str_replace;
use Symfony\Component\Filesystem\Path;

/**
 * @internal
 */
final readonly class GitHubAnnotationsReporter implements LineMutationTestingResultsReporter
{
    public const string DEFAULT_OUTPUT = 'php://stdout';

    public function __construct(
        private ResultsCollector $resultsCollector,
        private string $projectDirectory,
    ) {
    }

    public function getLines(): array
    {
        return array_map(
            $this->mapToAnnotation(...),
            $this->resultsCollector->getEscapedExecutionResults(),
        );
    }

    private function mapToAnnotation(MutantExecutionResult $result): string
    {
        return self::buildAnnotation(
            Path::makeRelative(
                $result->getOriginalFilePath(),
                $this->projectDirectory,
            ),
            $result->getOriginalStartingLine(),
            <<<TEXT
                Escaped Mutant for Mutator "{$result->getMutatorName()}":

                {$result->getMutantDiff()}
                TEXT,
        );
    }

    private static function buildAnnotation(
        string $filePath,
        int $line,
        string $message,
    ): string {
        // Data and properties need to be escaped to avoid a file path or error message to hijack
        // the GitHub annotations by leveraging the annotation delimiters.
        //
        // See:
        // https://docs.github.com/en/actions/reference/workflows-and-actions/workflow-commands#about-workflow-commands
        // https://github.com/actions/toolkit/blob/193fa46c20fde8b0ed54194bc08b841c78c0776d/packages/core/src/command.ts#L92-L111
        return sprintf(
            // The format is:
            // ::workflow-command parameter1={data},parameter2={data}::{command value}
            "::warning file=%s,line=%d::%s\n",
            self::escapeParameterValue($filePath),
            $line,
            self::escapeCommandValue($message),
        );
    }

    private static function escapeCommandValue(string $value): string
    {
        return str_replace(['%', "\r", "\n"], ['%25', '%0D', '%0A'], $value);
    }

    private static function escapeParameterValue(string $value): string
    {
        return str_replace(['%', "\r", "\n", ':', ','], ['%25', '%0D', '%0A', '%3A', '%2C'], $value);
    }
}
