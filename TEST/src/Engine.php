<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection;

use function explode;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapter;
use _HumbugBox9658796bb9f0\Infection\Configuration\Configuration;
use _HumbugBox9658796bb9f0\Infection\Console\ConsoleOutput;
use _HumbugBox9658796bb9f0\Infection\Event\ApplicationExecutionWasFinished;
use _HumbugBox9658796bb9f0\Infection\Event\EventDispatcher\EventDispatcher;
use _HumbugBox9658796bb9f0\Infection\Metrics\MetricsCalculator;
use _HumbugBox9658796bb9f0\Infection\Metrics\MinMsiChecker;
use _HumbugBox9658796bb9f0\Infection\Metrics\MinMsiCheckFailed;
use _HumbugBox9658796bb9f0\Infection\Mutation\MutationGenerator;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\IgnoreNode\NodeIgnorer;
use _HumbugBox9658796bb9f0\Infection\Process\Runner\InitialTestsFailed;
use _HumbugBox9658796bb9f0\Infection\Process\Runner\InitialTestsRunner;
use _HumbugBox9658796bb9f0\Infection\Process\Runner\MutationTestingRunner;
use _HumbugBox9658796bb9f0\Infection\Resource\Memory\MemoryLimiter;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\CoverageChecker;
use _HumbugBox9658796bb9f0\Infection\TestFramework\IgnoresAdditionalNodes;
use _HumbugBox9658796bb9f0\Infection\TestFramework\ProvidesInitialRunOnlyOptions;
use _HumbugBox9658796bb9f0\Infection\TestFramework\TestFrameworkExtraOptionsFilter;
final class Engine
{
    public function __construct(private Configuration $config, private TestFrameworkAdapter $adapter, private CoverageChecker $coverageChecker, private EventDispatcher $eventDispatcher, private InitialTestsRunner $initialTestsRunner, private MemoryLimiter $memoryLimiter, private MutationGenerator $mutationGenerator, private MutationTestingRunner $mutationTestingRunner, private MinMsiChecker $minMsiChecker, private ConsoleOutput $consoleOutput, private MetricsCalculator $metricsCalculator, private TestFrameworkExtraOptionsFilter $testFrameworkExtraOptionsFilter)
    {
    }
    public function execute() : void
    {
        $this->runInitialTestSuite();
        $this->runMutationAnalysis();
        try {
            $this->minMsiChecker->checkMetrics($this->metricsCalculator->getTestedMutantsCount(), $this->metricsCalculator->getMutationScoreIndicator(), $this->metricsCalculator->getCoveredCodeMutationScoreIndicator(), $this->consoleOutput);
        } finally {
            $this->eventDispatcher->dispatch(new ApplicationExecutionWasFinished());
        }
    }
    private function runInitialTestSuite() : void
    {
        if ($this->config->shouldSkipInitialTests()) {
            $this->consoleOutput->logSkippingInitialTests();
            $this->coverageChecker->checkCoverageExists();
            return;
        }
        $initialTestSuiteProcess = $this->initialTestsRunner->run($this->config->getTestFrameworkExtraOptions(), $this->getInitialTestsPhpOptionsArray(), $this->config->shouldSkipCoverage());
        if (!$initialTestSuiteProcess->isSuccessful()) {
            throw InitialTestsFailed::fromProcessAndAdapter($initialTestSuiteProcess, $this->adapter);
        }
        $this->coverageChecker->checkCoverageHasBeenGenerated($initialTestSuiteProcess->getCommandLine(), $initialTestSuiteProcess->getOutput());
        $this->memoryLimiter->limitMemory($initialTestSuiteProcess->getOutput(), $this->adapter);
    }
    private function getInitialTestsPhpOptionsArray() : array
    {
        return explode(' ', (string) $this->config->getInitialTestsPhpOptions());
    }
    private function runMutationAnalysis() : void
    {
        $mutations = $this->mutationGenerator->generate($this->config->mutateOnlyCoveredCode(), $this->getNodeIgnorers());
        $this->mutationTestingRunner->run($mutations, $this->getFilteredExtraOptionsForMutant());
    }
    private function getNodeIgnorers() : array
    {
        if ($this->adapter instanceof IgnoresAdditionalNodes) {
            return $this->adapter->getNodeIgnorers();
        }
        return [];
    }
    private function getFilteredExtraOptionsForMutant() : string
    {
        if ($this->adapter instanceof ProvidesInitialRunOnlyOptions) {
            return $this->testFrameworkExtraOptionsFilter->filterForMutantProcess($this->config->getTestFrameworkExtraOptions(), $this->adapter->getInitialRunOnlyOptions());
        }
        return $this->config->getTestFrameworkExtraOptions();
    }
}
