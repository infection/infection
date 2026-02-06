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

use function floor;
use Infection\Metrics\MetricsCalculator;
use function str_pad;
use const STR_PAD_LEFT;
use function str_repeat;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final readonly class MetricsReporter implements Reporter
{
    private const PAD_LENGTH = 8;

    private const LOW_QUALITY_THRESHOLD = 50;

    private const MEDIUM_QUALITY_THRESHOLD = 90;

    public function __construct(
        private OutputInterface $output,
        private MetricsCalculator $metricsCalculator,
        private bool $withUncovered,
    ) {
    }

    // Used to be MutationTestingConsoleLoggerSubscriber::showMetrics
    public function report(): void
    {
        $this->output->writeln(['', '']);
        $this->output->writeln('<options=bold>' . $this->metricsCalculator->getTotalMutantsCount() . '</options=bold> mutations were generated:');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getKilledByTestsCount()) . '</options=bold> mutants were killed by Test Framework');

        if ($this->metricsCalculator->getKilledByStaticAnalysisCount() > 0) {
            $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getKilledByStaticAnalysisCount()) . '</options=bold> mutants were caught by Static Analysis');
        }

        if ($this->metricsCalculator->getIgnoredCount() > 0) {
            $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getIgnoredCount()) . '</options=bold> mutants were configured to be ignored');
        }

        if ($this->metricsCalculator->getNotTestedCount() > 0) {
            $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getNotTestedCount()) . '</options=bold> mutants were not covered by tests');
        }

        if ($this->metricsCalculator->getEscapedCount() > 0) {
            $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getEscapedCount()) . '</options=bold> covered mutants were not detected');
        }

        if ($this->metricsCalculator->getErrorCount() > 0) {
            $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getErrorCount()) . '</options=bold> errors were encountered');
        }

        if ($this->metricsCalculator->getSyntaxErrorCount() > 0) {
            $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getSyntaxErrorCount()) . '</options=bold> syntax errors were encountered');
        }

        if ($this->metricsCalculator->getTimedOutCount() > 0) {
            $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getTimedOutCount()) . '</options=bold> time outs were encountered');
        }

        if ($this->metricsCalculator->getSkippedCount() > 0) {
            $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getSkippedCount()) . '</options=bold> mutants required more time than configured');
        }

        $mutationScoreIndicator = floor($this->metricsCalculator->getMutationScoreIndicator());
        $msiTag = $this->getPercentageTag($mutationScoreIndicator);

        $coverageRate = floor($this->metricsCalculator->getCoverageRate());
        $mutationCoverageTag = $this->getPercentageTag($coverageRate);

        $coveredMsi = floor($this->metricsCalculator->getCoveredCodeMutationScoreIndicator());
        $coveredMsiTag = $this->getPercentageTag($coveredMsi);

        $this->output->writeln(['', 'Metrics:']);

        if ($this->withUncovered) {
            $this->output->writeln(
                $this->addIndentation("Mutation Score Indicator (MSI): <{$msiTag}>{$mutationScoreIndicator}%</{$msiTag}>"),
            );
        }

        $this->output->writeln(
            $this->addIndentation("Mutation Code Coverage: <{$mutationCoverageTag}>{$coverageRate}%</{$mutationCoverageTag}>"),
        );

        $this->output->writeln(
            $this->addIndentation("Covered Code MSI: <{$coveredMsiTag}>{$coveredMsi}%</{$coveredMsiTag}>"),
        );
    }

    private function getPadded(int|string $subject, int $padLength = self::PAD_LENGTH): string
    {
        return str_pad((string) $subject, $padLength, ' ', STR_PAD_LEFT);
    }

    private function addIndentation(string $string): string
    {
        return str_repeat(' ', self::PAD_LENGTH + 1) . $string;
    }

    private function getPercentageTag(float $percentage): string
    {
        if ($percentage >= 0 && $percentage < self::LOW_QUALITY_THRESHOLD) {
            return 'low';
        }

        if ($percentage >= self::LOW_QUALITY_THRESHOLD && $percentage < self::MEDIUM_QUALITY_THRESHOLD) {
            return 'medium';
        }

        return 'high';
    }
}
