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

use Infection\Mutant\MutantExecutionResult;
use const PHP_EOL;
use function sprintf;
use function trim;

/**
 * Uses the GitHub Actions line grouping feature to make the output more digestable and collapsable.
 *
 * @see https://docs.github.com/en/actions/reference/workflows-and-actions/workflow-commands#grouping-log-lines
 *
 * @internal
 */
final readonly class GitHubActionsLogTextFileLogger extends BaseTextFileLogger
{
    protected function getHeadlineLines(string $headlinePrefix): string
    {
        return '';
    }

    /**
     * @param MutantExecutionResult[] $executionResults
     */
    protected function getResultsLine(
        array $executionResults,
        string $headlinePrefix,
        bool &$separateSections,
    ): string {
        $results = trim(parent::getResultsLine($executionResults, $headlinePrefix, $separateSections));

        if ($results === '') {
            return sprintf('0 %s mutants' . PHP_EOL, $headlinePrefix);
        }

        return sprintf('::group::%s mutants' . PHP_EOL . '%s' . PHP_EOL . '::endgroup::' . PHP_EOL, $headlinePrefix, $results);
    }
}
