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

namespace Infection\Event\Subscriber;

use function floor;
use Generator;
use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Differ\DiffColorizer;
use Infection\Event\MutantProcessWasFinished;
use Infection\Event\MutationTestingWasFinished;
use Infection\Event\MutationTestingWasStarted;
use Infection\Logger\FederatedLogger;
use Infection\Logger\FileLogger;
use Infection\Logger\MutationTestingResultsLogger;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\ResultsCollector;
use Infection\Mutant\MutantExecutionResult;
use function iterator_to_array;
use function sprintf;
use function str_pad;
use const STR_PAD_LEFT;
use function str_repeat;
use function str_starts_with;
use function strlen;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class MutationTestingConsoleLoggerSubscriber implements EventSubscriber
{
    private const PAD_LENGTH = 8;

    private const LOW_QUALITY_THRESHOLD = 50;
    private const MEDIUM_QUALITY_THRESHOLD = 90;

    private int $mutationCount = 0;

    public function __construct(
        private readonly OutputInterface $output,
        private readonly OutputFormatter $outputFormatter,
        private readonly MetricsCalculator $metricsCalculator,
        private readonly ResultsCollector $resultsCollector,
        private readonly DiffColorizer $diffColorizer,
        private readonly FederatedLogger $mutationTestingResultsLogger,
        private readonly bool $showMutations)
    {
    }

    public function onMutationTestingWasStarted(MutationTestingWasStarted $event): void
    {
        $this->mutationCount = $event->getMutationCount();

        $this->outputFormatter->start($this->mutationCount);
    }

    public function onMutantProcessWasFinished(MutantProcessWasFinished $event): void
    {
        $executionResult = $event->getExecutionResult();

        $this->outputFormatter->advance($executionResult, $this->mutationCount);
    }

    public function onMutationTestingWasFinished(MutationTestingWasFinished $event): void
    {
        $this->outputFormatter->finish();

        if ($this->showMutations) {
            $this->showMutations($this->resultsCollector->getEscapedExecutionResults(), 'Escaped');

            if ($this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $this->showMutations($this->resultsCollector->getNotCoveredExecutionResults(), 'Not covered');
            }
        }

        $this->showMetrics();
        $this->showGeneratedLogFiles();

        $this->output->writeln(['', 'Please note that some mutants will inevitably be harmless (i.e. false positives).']);
    }

    /**
     * @param MutantExecutionResult[] $executionResults
     */
    private function showMutations(array $executionResults, string $headlinePrefix): void
    {
        if ($executionResults === []) {
            return;
        }

        $headline = sprintf('%s mutants:', $headlinePrefix);

        $this->output->writeln([
            '',
            $headline,
            str_repeat('=', strlen($headline)),
            '',
        ]);

        foreach ($executionResults as $index => $executionResult) {
            $this->output->writeln([
                '',
                sprintf(
                    '%d) %s:%d    [M] %s',
                    $index + 1,
                    $executionResult->getOriginalFilePath(),
                    $executionResult->getOriginalStartingLine(),
                    $executionResult->getMutatorName(),
                ),
            ]);

            $this->output->writeln($this->diffColorizer->colorize($executionResult->getMutantDiff()));
        }
    }

    private function showMetrics(): void
    {
        $this->output->writeln(['', '']);
        $this->output->writeln('<options=bold>' . $this->metricsCalculator->getTotalMutantsCount() . '</options=bold> mutations were generated:');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getKilledCount()) . '</options=bold> mutants were killed');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getIgnoredCount()) . '</options=bold> mutants were configured to be ignored');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getNotTestedCount()) . '</options=bold> mutants were not covered by tests');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getEscapedCount()) . '</options=bold> covered mutants were not detected');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getErrorCount()) . '</options=bold> errors were encountered');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getSyntaxErrorCount()) . '</options=bold> syntax errors were encountered');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getTimedOutCount()) . '</options=bold> time outs were encountered');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getSkippedCount()) . '</options=bold> mutants required more time than configured');

        $mutationScoreIndicator = floor($this->metricsCalculator->getMutationScoreIndicator());
        $msiTag = $this->getPercentageTag($mutationScoreIndicator);

        $coverageRate = floor($this->metricsCalculator->getCoverageRate());
        $mutationCoverageTag = $this->getPercentageTag($coverageRate);

        $coveredMsi = floor($this->metricsCalculator->getCoveredCodeMutationScoreIndicator());
        $coveredMsiTag = $this->getPercentageTag($coveredMsi);

        $this->output->writeln(['', 'Metrics:']);

        $this->output->writeln(
            $this->addIndentation("Mutation Score Indicator (MSI): <{$msiTag}>{$mutationScoreIndicator}%</{$msiTag}>"),
        );

        $this->output->writeln(
            $this->addIndentation("Mutation Code Coverage: <{$mutationCoverageTag}>{$coverageRate}%</{$mutationCoverageTag}>"),
        );

        $this->output->writeln(
            $this->addIndentation("Covered Code MSI: <{$coveredMsiTag}>{$coveredMsi}%</{$coveredMsiTag}>"),
        );
    }

    private function showGeneratedLogFiles(): void
    {
        /** @var FileLogger[] $fileLoggers */
        $fileLoggers = iterator_to_array($this->getFileLoggers($this->mutationTestingResultsLogger->getLoggers()));

        if ($fileLoggers !== []) {
            $this->output->writeln(['', 'Generated Reports:']);

            foreach ($fileLoggers as $fileLogger) {
                $this->output->writeln(
                    $this->addIndentation(sprintf('- %s', $fileLogger->getFilePath())),
                );
            }

            return;
        }

        // for the case when no file loggers are configured and `--show-mutations` is not used
        if (!$this->showMutations) {
            $this->output->writeln(['', 'Note: to see escaped mutants run Infection with "--show-mutations" or configure file loggers.']);
        }
    }

    /**
     * @param array<int, MutationTestingResultsLogger> $allLoggers
     *
     * @return Generator<MutationTestingResultsLogger>
     */
    private function getFileLoggers(array $allLoggers): Generator
    {
        foreach ($allLoggers as $logger) {
            if ($logger instanceof FederatedLogger) {
                yield from $this->getFileLoggers($logger->getLoggers());
            } elseif ($logger instanceof FileLogger && !str_starts_with($logger->getFilePath(), 'php://')) {
                yield $logger;
            }
        }
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
