<?php

declare(strict_types=1);

namespace Infection\Process\Listener;

use Infection\EventDispatcher\EventSubscriberInterface;
use Infection\Events\MutationTestingFinished;
use Infection\Events\MutationTestingStarted;
use Infection\Events\MutantProcessFinished;
use Infection\Mutant\MetricsCalculator;
use Infection\Process\MutantProcess;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class MutationConsoleLoggerSubscriber implements EventSubscriberInterface
{
    const PAD_LENGTH = 8;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * @var MutantProcess[]
     */
    private $mutantProcesses = [];

    /**
     * @var MetricsCalculator
     */
    private $metricsCalculator;

    public function __construct(OutputInterface $output, ProgressBar $progressBar, MetricsCalculator $metricsCalculator)
    {
        $this->output = $output;
        $this->progressBar = $progressBar;
        $this->metricsCalculator = $metricsCalculator;
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
        $this->progressBar->start($event->getMutationCount());
    }

    public function onMutantProcessFinished(MutantProcessFinished $event)
    {
        $this->progressBar->advance();

        $this->mutantProcesses[] = $event->getMutantProcess();

        $this->metricsCalculator->collect($event->getMutantProcess());
    }

    public function onMutationTestingFinished(MutationTestingFinished $event)
    {
        $this->progressBar->finish();
        // TODO [doc] write test -> run mutation for just this file. Should be 100%, 100%, 100%,

        $this->output->writeln('');
        $this->output->writeln('<options=bold>' . $this->metricsCalculator->getTotalMutantsCount() . '</options=bold> mutations were generated:');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getKilledCount()) . '</options=bold> mutants were killed');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getNotCoveredByTestsCount()) . '</options=bold> mutants were not covered by tests');
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getEscapedCount()) . '</options=bold> covered mutants were not detected');
//        $this->output->writeln($this->getPadded($errorCount) . ' fatal errors were encountered'); // TODO
        $this->output->writeln('<options=bold>' . $this->getPadded($this->metricsCalculator->getTimedOutCount()) . '</options=bold> time outs were encountered');

        $this->output->writeln(['', 'Metrics:']);
        $this->output->writeln($this->addIndentation('Mutation Score Indicator (MSI): <options=bold>' . $this->metricsCalculator->getMutationScoreIndicator() . '%</options=bold>'));
        $this->output->writeln($this->addIndentation('Mutation Code Coverage: <options=bold>' . $this->metricsCalculator->getCoverageRate() . '%</options=bold>'));
        $this->output->writeln($this->addIndentation('Covered Code MSI: <options=bold>' . $this->metricsCalculator->getCoveredCodeMutationScoreIndicator() . '%</options=bold>'));

        $this->output->writeln('');
        $this->output->writeln('Please note that some mutants will inevitably be harmless (i.e. false positives).');
    }

    private function getPadded($subject, int $padLength = self::PAD_LENGTH): string
    {
        return str_pad((string) $subject, $padLength, ' ', STR_PAD_LEFT);
    }

    private function addIndentation(string $string): string
    {
        return str_repeat(' ', self::PAD_LENGTH + 1) . $string;
    }
}