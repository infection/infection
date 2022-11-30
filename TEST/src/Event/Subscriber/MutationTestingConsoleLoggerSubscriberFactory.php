<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event\Subscriber;

use _HumbugBox9658796bb9f0\Infection\Console\OutputFormatter\OutputFormatter;
use _HumbugBox9658796bb9f0\Infection\Differ\DiffColorizer;
use _HumbugBox9658796bb9f0\Infection\Logger\FederatedLogger;
use _HumbugBox9658796bb9f0\Infection\Metrics\MetricsCalculator;
use _HumbugBox9658796bb9f0\Infection\Metrics\ResultsCollector;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
final class MutationTestingConsoleLoggerSubscriberFactory implements SubscriberFactory
{
    public function __construct(private MetricsCalculator $metricsCalculator, private ResultsCollector $resultsCollector, private DiffColorizer $diffColorizer, private FederatedLogger $mutationTestingResultsLogger, private bool $showMutations, private OutputFormatter $formatter)
    {
    }
    public function create(OutputInterface $output) : EventSubscriber
    {
        return new MutationTestingConsoleLoggerSubscriber($output, $this->formatter, $this->metricsCalculator, $this->resultsCollector, $this->diffColorizer, $this->mutationTestingResultsLogger, $this->showMutations);
    }
}
