<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2019, Maks Rafalko
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

namespace Infection\Process\Listener;

use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Differ\DiffColorizer;
use Infection\EventDispatcher\EventSubscriberInterface;
use Infection\Events\MutantProcessFinished;
use Infection\Events\MutationTestingFinished;
use Infection\Events\MutationTestingStarted;
use Infection\Mutant\MetricsCalculator;
use Infection\Process\MutantProcessInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class MutationTestingConsoleLoggerSubscriber implements EventSubscriberInterface
{
    private const PAD_LENGTH = 8;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var OutputFormatter
     */
    private $outputFormatter;

    /**
     * @var MutantProcessInterface[]
     */
    private $mutantProcesses = [];

    /**
     * @var MetricsCalculator
     */
    private $metricsCalculator;

    /**
     * @var bool
     */
    private $showMutations;

    /**
     * @var DiffColorizer
     */
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
            MutationTestingStarted::class => [$this, 'onMutationTestingStarted'],
            MutationTestingFinished::class => [$this, 'onMutationTestingFinished'],
            MutantProcessFinished::class => [$this, 'onMutantProcessFinished'],
        ];
    }

    public function onMutationTestingStarted(MutationTestingStarted $event): void
    {
        $this->mutationCount = $event->getMutationCount();

        $this->outputFormatter->start($this->mutationCount);
    }

    public function onMutantProcessFinished(MutantProcessFinished $event): void
    {
        $this->mutantProcesses[] = $event->getMutantProcess();
        $this->metricsCalculator->collect($event->getMutantProcess());

        $this->outputFormatter->advance($event->getMutantProcess(), $this->mutationCount);
    }

    public function onMutationTestingFinished(MutationTestingFinished $event): void
    {
        $this->outputFormatter->finish();

        if ($this->showMutations) {
            $this->showMutations($this->metricsCalculator->getEscapedMutantProcesses(), 'Escaped');

            if ($this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $this->showMutations($this->metricsCalculator->getNotCoveredMutantProcesses(), 'Not covered');
            }
        }

        $this->showMetrics();
    }

    /**
     * @param MutantProcessInterface[] $processes
     */
    private function showMutations(array $processes, string $headlinePrefix): void
    {
        $headline = sprintf('%s mutants:', $headlinePrefix);

        $this->output->writeln([
            '',
            $headline,
            str_repeat('=', \strlen($headline)),
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
                    $mutation->getMutator()::getName()
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

    private function getPadded($subject, int $padLength = self::PAD_LENGTH): string
    {
        return str_pad((string) $subject, $padLength, ' ', STR_PAD_LEFT);
    }

    private function addIndentation(string $string): string
    {
        return str_repeat(' ', self::PAD_LENGTH + 1) . $string;
    }

    private function getPercentageTag(float $percentage)
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
