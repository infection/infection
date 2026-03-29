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

namespace Infection\Logger\MutationAnalysis;

use Infection\Framework\Iterable\IterableCounter;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutation\Mutation;
use function sprintf;
use function str_repeat;
use function strlen;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class ConsoleDotLogger extends AbstractMutationAnalysisLogger
{
    private const DOTS_PER_ROW = 50;

    private ?int $mutationCount = null;

    public function __construct(
        private readonly OutputInterface $output,
    ) {
    }

    public function startAnalysis(int $mutationCount): void
    {
        parent::startAnalysis($mutationCount);

        $this->mutationCount = $mutationCount;

        $this->output->writeln([
            '',
            '<killed>.</killed>: killed by tests, '
            . '<killed-by-static-analysis>A</killed-by-static-analysis>: killed by SA, '
            . '<escaped>M</escaped>: escaped, '
            . '<uncovered>U</uncovered>: uncovered',
            '<with-error>E</with-error>: fatal error, '
            . '<with-syntax-error>X</with-syntax-error>: syntax error, '
            . '<timeout>T</timeout>: timed out, '
            . '<skipped>S</skipped>: skipped, '
            . '<ignored>I</ignored>: ignored',
            '',
        ]);
    }

    public function startEvaluation(Mutation $mutation): void
    {
        // Do nothing.
    }

    public function finishEvaluation(MutantExecutionResult $executionResult): void
    {
        parent::finishEvaluation($executionResult);

        $mutationCount = $this->mutationCount;
        Assert::notNull($mutationCount);

        $this->output->write(
            self::getCharacter($executionResult),
        );

        $remainder = $this->callsCount % self::DOTS_PER_ROW;
        $endOfRow = $remainder === 0;
        $lastDot = $mutationCount === $this->callsCount;

        if ($lastDot && !$endOfRow) {
            $this->output->write(str_repeat(' ', self::DOTS_PER_ROW - $remainder));
        }

        if ($lastDot || $endOfRow) {
            if ($mutationCount === IterableCounter::UNKNOWN_COUNT) {
                $this->output->write(sprintf('   (%5d)', $this->callsCount)); // 5 because folks with over 10k mutations have more important problems
            } else {
                $length = strlen((string) $mutationCount);
                $format = sprintf('   (%%%dd / %%%dd)', $length, $length);

                $this->output->write(sprintf($format, $this->callsCount, $mutationCount));
            }

            if ($this->callsCount !== $mutationCount) {
                $this->output->writeln('');
            }
        }
    }

    private static function getCharacter(MutantExecutionResult $executionResult): string
    {
        return match ($executionResult->getDetectionStatus()) {
            DetectionStatus::KILLED_BY_TESTS => '<killed>.</killed>',
            DetectionStatus::KILLED_BY_STATIC_ANALYSIS => '<killed-by-static-analysis>A</killed-by-static-analysis>',
            DetectionStatus::NOT_COVERED => '<uncovered>U</uncovered>',
            DetectionStatus::ESCAPED => '<escaped>M</escaped>',
            DetectionStatus::TIMED_OUT => '<timeout>T</timeout>',
            DetectionStatus::SKIPPED => '<skipped>S</skipped>',
            DetectionStatus::ERROR => '<with-error>E</with-error>',
            DetectionStatus::SYNTAX_ERROR => '<with-syntax-error>X</with-syntax-error>',
            DetectionStatus::IGNORED => '<ignored>I</ignored>',
        };
    }
}
