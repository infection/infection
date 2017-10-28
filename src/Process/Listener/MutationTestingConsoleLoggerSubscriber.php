<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Process\Listener;

use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Differ\DiffColorizer;
use Infection\EventDispatcher\EventSubscriberInterface;
use Infection\Events\MutationTestingFinished;
use Infection\Events\MutationTestingStarted;
use Infection\Events\MutantProcessFinished;
use Infection\Mutant\MetricsCalculator;
use Infection\Process\MutantProcess;
use Symfony\Component\Console\Output\OutputInterface;

class MutationTestingConsoleLoggerSubscriber implements EventSubscriberInterface
{
    const PAD_LENGTH = 8;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var OutputFormatter
     */
    private $outputFormatter;

    /**
     * @var MutantProcess[]
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

    public function __construct(OutputInterface $output, OutputFormatter $outputFormatter, MetricsCalculator $metricsCalculator, DiffColorizer $diffColorizer, bool $showMutations)
    {
        $this->output = $output;
        $this->outputFormatter = $outputFormatter;
        $this->metricsCalculator = $metricsCalculator;
        $this->showMutations = $showMutations;
        $this->diffColorizer = $diffColorizer;

        $this->mutationCount = 0;
    }

    public function getSubscribedEvents()
    {
        return [
            MutationTestingStarted::class => [$this, 'onMutationTestingStarted'],
            MutationTestingFinished::class => [$this, 'onMutationTestingFinished'],
            MutantProcessFinished::class => [$this, 'onMutantProcessFinished'],
        ];
    }

    public function onMutationTestingStarted(MutationTestingStarted $event)
    {
        $this->mutationCount = $event->getMutationCount();

        $this->outputFormatter->start($this->mutationCount);
    }

    public function onMutantProcessFinished(MutantProcessFinished $event)
    {
        $this->mutantProcesses[] = $event->getMutantProcess();
        $this->metricsCalculator->collect($event->getMutantProcess());

        $this->outputFormatter->advance($event->getMutantProcess(), $this->mutationCount);
    }

    public function onMutationTestingFinished(MutationTestingFinished $event)
    {
        // TODO [doc] write test -> run mutation for just this file. Should be 100%, 100%, 100%,
        $this->outputFormatter->finish();

        if ($this->showMutations) {
            $this->showMutations($this->metricsCalculator->getEscapedMutantProcesses(), 'Escaped');

            if ($this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $this->showMutations($this->metricsCalculator->getNotCoveredMutantProcesses(), 'Not covered');
            }
        }

        $this->showMetrics();
    }

    private function showMutations(array $processes, string $headlinePrefix)
    {
        $headline = sprintf('%s mutants:', $headlinePrefix);

        $this->output->writeln([
            '',
            $headline,
            str_repeat('=', strlen($headline)),
            '',
        ]);

        foreach ($processes as $index => $mutantProcess) {
            $this->output->writeln([
                '',
                sprintf('%d) %s', $index + 1, get_class($mutantProcess->getMutant()->getMutation()->getMutator())),
            ]);
            $this->output->writeln($mutantProcess->getMutant()->getMutation()->getOriginalFilePath());
            $this->output->writeln($this->diffColorizer->colorize($mutantProcess->getMutant()->getDiff()));
        }
    }

    private function showMetrics()
    {
        $this->output->writeln(['', '']);
        $this->output->writeln('<options=bold>' . $this->metricsCalculator->getTotalMutantsCount() . '</options=bold> mutations were generated:');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getKilledCount()) . '</options=bold> mutants were killed');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getNotCoveredByTestsCount()) . '</options=bold> mutants were not covered by tests');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getEscapedCount()) . '</options=bold> covered mutants were not detected');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getErrorCount()) . '</options=bold> errors were encountered');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getTimedOutCount()) . '</options=bold> time outs were encountered');

        $msiTag = $this->getPercentageTag($this->metricsCalculator->getMutationScoreIndicator());
        $mutationCoverageTag = $this->getPercentageTag($this->metricsCalculator->getCoverageRate());
        $coveredMsiTag = $this->getPercentageTag($this->metricsCalculator->getCoveredCodeMutationScoreIndicator());

        $this->output->writeln(['', 'Metrics:']);
        $this->output->writeln(
            $this->addIndentation("Mutation Score Indicator (MSI): <{$msiTag}>{$this->metricsCalculator->getMutationScoreIndicator()}%</{$msiTag}>")
        );
        $this->output->writeln(
            $this->addIndentation("Mutation Code Coverage: <{$mutationCoverageTag}>{$this->metricsCalculator->getCoverageRate()}%</{$mutationCoverageTag}>")
        );
        $this->output->writeln(
            $this->addIndentation("Covered Code MSI: <{$coveredMsiTag}>{$this->metricsCalculator->getCoveredCodeMutationScoreIndicator()}%</{$coveredMsiTag}>")
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
