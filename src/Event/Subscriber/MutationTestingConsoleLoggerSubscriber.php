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
use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Differ\DiffColorizer;
use Infection\Event\MutantProcessWasFinished;
use Infection\Event\MutationTestingWasFinished;
use Infection\Event\MutationTestingWasStarted;
use Infection\Mutant\MetricsCalculator;
use Infection\Mutant\MutantExecutionResult;
use Infection\Process\MutantProcess;
use function Safe\sprintf;
use function str_pad;
use function str_repeat;
use function strlen;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class MutationTestingConsoleLoggerSubscriber implements EventSubscriber
{
    private const PAD_LENGTH = 8;

    /**
     * @var MutantExecutionResult[]
     */
    private $mutantExecutionResults = [];
    private $output;
    private $outputFormatter;
    private $metricsCalculator;
    private $showMutations;
    private $diffColorizer;

    /**
     * @var int
     */
    private $mutationCount = 0;

    public function __construct(
        OutputInterface $output,
        OutputFormatter $outputFormatter,
        MetricsCalculator $metricsCalculator,
        DiffColorizer $diffColorizer,
        bool $showMutations
    ) {
        $this->output = $output;
        $this->outputFormatter = $outputFormatter;
        $this->metricsCalculator = $metricsCalculator;
        $this->showMutations = $showMutations;
        $this->diffColorizer = $diffColorizer;
    }

    public function getSubscribedEvents(): array
    {
        return [
            MutationTestingWasStarted::class => [$this, 'onMutationTestingWasStarted'],
            MutationTestingWasFinished::class => [$this, 'onMutationTestingWasFinished'],
            MutantProcessWasFinished::class => [$this, 'onMutantProcessWasFinished'],
        ];
    }

    public function onMutationTestingWasStarted(MutationTestingWasStarted $event): void
    {
        $this->mutationCount = $event->getMutationCount();

        $this->outputFormatter->start($this->mutationCount);
    }

    public function onMutantProcessWasFinished(MutantProcessWasFinished $event): void
    {
        $this->mutantExecutionResults[] = $event->getExecutionResult();
        $this->metricsCalculator->collect($event->getExecutionResult());

        $this->outputFormatter->advance($event->getExecutionResult(), $this->mutationCount);
    }

    public function onMutationTestingWasFinished(MutationTestingWasFinished $event): void
    {
        $this->outputFormatter->finish();

        if ($this->showMutations) {
            $this->showMutations($this->metricsCalculator->getEscapedMutantExecutionResults(), 'Escaped');

            if ($this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $this->showMutations($this->metricsCalculator->getNotCoveredMutantExecutionResults(), 'Not covered');
            }
        }

        $this->showMetrics();
    }

    /**
     * @param MutantProcess[] $processes
     */
    private function showMutations(array $processes, string $headlinePrefix): void
    {
        $headline = sprintf('%s mutants:', $headlinePrefix);

        $this->output->writeln([
            '',
            $headline,
            str_repeat('=', strlen($headline)),
            '',
        ]);

        foreach ($processes as $index => $mutantProcess) {
            $mutation = $mutantProcess->getMutant()->getMutation();

            $this->output->writeln([
                '',
                sprintf(
                    '%d) %s:%d    [M] %s',
                    $index + 1,
                    $mutation->getOriginalFilePath(),
                    (int) $mutation->getAttributes()['startLine'],
                    $mutation->getMutatorName()
                ),
            ]);

            $this->output->writeln($this->diffColorizer->colorize($mutantProcess->getMutant()->getDiff()));
        }
    }

    private function showMetrics(): void
    {
        $this->output->writeln(['', '']);
        $this->output->writeln('<options=bold>' . $this->metricsCalculator->getTotalMutantsCount() . '</options=bold> mutations were generated:');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getKilledCount()) . '</options=bold> mutants were killed');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getNotCoveredByTestsCount()) . '</options=bold> mutants were not covered by tests');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getEscapedCount()) . '</options=bold> covered mutants were not detected');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getErrorCount()) . '</options=bold> errors were encountered');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getTimedOutCount()) . '</options=bold> time outs were encountered');

        $mutationScoreIndicator = floor($this->metricsCalculator->getMutationScoreIndicator());
        $msiTag = $this->getPercentageTag($mutationScoreIndicator);

        $coverageRate = floor($this->metricsCalculator->getCoverageRate());
        $mutationCoverageTag = $this->getPercentageTag($coverageRate);

        $coveredMsi = floor($this->metricsCalculator->getCoveredCodeMutationScoreIndicator());
        $coveredMsiTag = $this->getPercentageTag($coveredMsi);

        $this->output->writeln(['', 'Metrics:']);

        $this->output->writeln(
            $this->addIndentation("Mutation Score Indicator (MSI): <{$msiTag}>{$mutationScoreIndicator}%</{$msiTag}>")
        );

        $this->output->writeln(
            $this->addIndentation("Mutation Code Coverage: <{$mutationCoverageTag}>{$coverageRate}%</{$mutationCoverageTag}>")
        );

        $this->output->writeln(
            $this->addIndentation("Covered Code MSI: <{$coveredMsiTag}>{$coveredMsi}%</{$coveredMsiTag}>")
        );

        $this->output->writeln(['', 'Please note that some mutants will inevitably be harmless (i.e. false positives).']);
    }

    /**
     * @param int|string $subject
     */
    private function getPadded($subject, int $padLength = self::PAD_LENGTH): string
    {
        return str_pad((string) $subject, $padLength, ' ', STR_PAD_LEFT);
    }

    private function addIndentation(string $string): string
    {
        return str_repeat(' ', self::PAD_LENGTH + 1) . $string;
    }

    private function getPercentageTag(float $percentage): string
    {
        if ($percentage >= 0 && $percentage < 50) {
            return 'low';
        }

        if ($percentage >= 50 && $percentage < 90) {
            return 'medium';
        }

        return 'high';
    }
}
