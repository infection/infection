<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event\Subscriber;

use function floor;
use Generator;
use _HumbugBox9658796bb9f0\Infection\Console\OutputFormatter\OutputFormatter;
use _HumbugBox9658796bb9f0\Infection\Differ\DiffColorizer;
use _HumbugBox9658796bb9f0\Infection\Event\MutantProcessWasFinished;
use _HumbugBox9658796bb9f0\Infection\Event\MutationTestingWasFinished;
use _HumbugBox9658796bb9f0\Infection\Event\MutationTestingWasStarted;
use _HumbugBox9658796bb9f0\Infection\Logger\FederatedLogger;
use _HumbugBox9658796bb9f0\Infection\Logger\FileLogger;
use _HumbugBox9658796bb9f0\Infection\Logger\MutationTestingResultsLogger;
use _HumbugBox9658796bb9f0\Infection\Metrics\MetricsCalculator;
use _HumbugBox9658796bb9f0\Infection\Metrics\ResultsCollector;
use _HumbugBox9658796bb9f0\Infection\Mutant\MutantExecutionResult;
use function iterator_to_array;
use function sprintf;
use function str_pad;
use const STR_PAD_LEFT;
use function str_repeat;
use function str_starts_with;
use function strlen;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
final class MutationTestingConsoleLoggerSubscriber implements EventSubscriber
{
    private const PAD_LENGTH = 8;
    private int $mutationCount = 0;
    public function __construct(private OutputInterface $output, private OutputFormatter $outputFormatter, private MetricsCalculator $metricsCalculator, private ResultsCollector $resultsCollector, private DiffColorizer $diffColorizer, private FederatedLogger $mutationTestingResultsLogger, private bool $showMutations)
    {
    }
    public function onMutationTestingWasStarted(MutationTestingWasStarted $event) : void
    {
        $this->mutationCount = $event->getMutationCount();
        $this->outputFormatter->start($this->mutationCount);
    }
    public function onMutantProcessWasFinished(MutantProcessWasFinished $event) : void
    {
        $executionResult = $event->getExecutionResult();
        $this->outputFormatter->advance($executionResult, $this->mutationCount);
    }
    public function onMutationTestingWasFinished(MutationTestingWasFinished $event) : void
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
    private function showMutations(array $executionResults, string $headlinePrefix) : void
    {
        if ($executionResults === []) {
            return;
        }
        $headline = sprintf('%s mutants:', $headlinePrefix);
        $this->output->writeln(['', $headline, str_repeat('=', strlen($headline)), '']);
        foreach ($executionResults as $index => $executionResult) {
            $this->output->writeln(['', sprintf('%d) %s:%d    [M] %s', $index + 1, $executionResult->getOriginalFilePath(), $executionResult->getOriginalStartingLine(), $executionResult->getMutatorName())]);
            $this->output->writeln($this->diffColorizer->colorize($executionResult->getMutantDiff()));
        }
    }
    private function showMetrics() : void
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
        $this->output->writeln($this->addIndentation("Mutation Score Indicator (MSI): <{$msiTag}>{$mutationScoreIndicator}%</{$msiTag}>"));
        $this->output->writeln($this->addIndentation("Mutation Code Coverage: <{$mutationCoverageTag}>{$coverageRate}%</{$mutationCoverageTag}>"));
        $this->output->writeln($this->addIndentation("Covered Code MSI: <{$coveredMsiTag}>{$coveredMsi}%</{$coveredMsiTag}>"));
    }
    private function showGeneratedLogFiles() : void
    {
        $fileLoggers = iterator_to_array($this->getFileLoggers($this->mutationTestingResultsLogger->getLoggers()));
        if ($fileLoggers !== []) {
            $this->output->writeln(['', 'Generated Reports:']);
            foreach ($fileLoggers as $fileLogger) {
                $this->output->writeln($this->addIndentation(sprintf('- %s', $fileLogger->getFilePath())));
            }
            return;
        }
        if (!$this->showMutations) {
            $this->output->writeln(['', 'Note: to see escaped mutants run Infection with "--show-mutations" or configure file loggers.']);
        }
    }
    private function getFileLoggers(array $allLoggers) : Generator
    {
        foreach ($allLoggers as $logger) {
            if ($logger instanceof FederatedLogger) {
                yield from $this->getFileLoggers($logger->getLoggers());
            } elseif ($logger instanceof FileLogger && !str_starts_with($logger->getFilePath(), 'php://')) {
                (yield $logger);
            }
        }
    }
    private function getPadded(int|string $subject, int $padLength = self::PAD_LENGTH) : string
    {
        return str_pad((string) $subject, $padLength, ' ', STR_PAD_LEFT);
    }
    private function addIndentation(string $string) : string
    {
        return str_repeat(' ', self::PAD_LENGTH + 1) . $string;
    }
    private function getPercentageTag(float $percentage) : string
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
