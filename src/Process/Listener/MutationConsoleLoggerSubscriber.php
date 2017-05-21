<?php

declare(strict_types=1);

namespace Infection\Process\Listener;

use Infection\EventDispatcher\EventSubscriberInterface;
use Infection\Events\MutationTestingFinished;
use Infection\Events\MutationTestingStarted;
use Infection\Events\MutantProcessFinished;
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
     * @var AbstractTestFrameworkAdapter
     */
    private $testFrameworkAdapter;

    public function __construct(OutputInterface $output, ProgressBar $progressBar, AbstractTestFrameworkAdapter $testFrameworkAdapter)
    {
        $this->output = $output;
        $this->progressBar = $progressBar;
        $this->testFrameworkAdapter = $testFrameworkAdapter;
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
    }

    public function onMutationTestingFinished(MutationTestingFinished $event)
    {
        $this->progressBar->finish();

        $killedCount = 0;
        $escapedCount = 0;
        $timedOutCount = 0;
        $notCoveredByTestsCount = 0;
        $totalMutantsCount = count($this->mutantProcesses);

        // TODO think about dispatching Result instead of process, this is much cleaner code wise
        // TODO introduce collector to move logic there and here just display

        foreach ($this->mutantProcesses as $process) {
            if (! $process->getMutant()->isCoveredByTest()) {
                $notCoveredByTestsCount++;
            } else if ($this->testFrameworkAdapter->testsPass($process->getProcess()->getOutput())) {
                $escapedCount++;

                echo $process->getMutant()->getMutation()->getOriginalFilePath() . "\n";
                echo $process->getMutant()->getDiff() . "\n";
                echo $process->getProcess()->getOutput() . "\n";

            } else if ($process->isTimedOut()) {
                $timedOutCount++;
            } else {
                $killedCount++;
            }
        }

        $this->output->writeln('');
        $this->output->writeln('<options=bold>' . $totalMutantsCount . '</options=bold> mutations were generated:');
        $this->output->writeln('<options=bold>' . $this->getPadded($killedCount) . '</options=bold> mutants were killed');
        $this->output->writeln('<options=bold>' . $this->getPadded($notCoveredByTestsCount) . '</options=bold> mutants were not covered by tests');
        $this->output->writeln('<options=bold>' . $this->getPadded($escapedCount) . '</options=bold> covered mutants were not detected');
//        $this->output->writeln($this->getPadded($errorCount) . ' fatal errors were encountered'); // TODO
        $this->output->writeln('<options=bold>' . $this->getPadded($timedOutCount) . '</options=bold> time outs were encountered');

        $vanquishedTotal = $killedCount + $timedOutCount/* + $errorCount*/;
        $measurableTotal = $totalMutantsCount - $notCoveredByTestsCount;
        $detectionRateTested  = 0;
        $coveredRate = 0;
        $detectionRateAll = 0;

        if ($measurableTotal !== 0) {
            $detectionRateTested  = round(100 * ($vanquishedTotal / $measurableTotal));
        }

        if ($totalMutantsCount) {
            $coveredRate = round(100 * ($measurableTotal / $totalMutantsCount));
            $detectionRateAll = round(100 * ($vanquishedTotal / $totalMutantsCount));
        }

        $this->output->writeln(['', 'Metrics:']);

        // $killedCount + $timeoutCount + $errorCount / $totalCount
        //                           $vanquishedTotal / $totalCount
        $this->output->writeln($this->addIndentation('Mutation Score Indicator (MSI): <options=bold>' . $detectionRateAll . '%</options=bold>'));

        // $totalCount - $notCoveredByTestsCount / $totalCount
        //                      $measurableTotal / $totalCount
        $this->output->writeln($this->addIndentation('Mutation Code Coverage: <options=bold>' . $coveredRate . '%</options=bold>'));

        // $killedCount + $timeoutCount + $errorCount / $totalCount - $notCoveredByTestsCount
        //                           $vanquishedTotal / $measurableTotal
        $this->output->writeln($this->addIndentation('Covered Code MSI: <options=bold>' . $detectionRateTested . '%</options=bold>'));

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