<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event\Subscriber;

use _HumbugBox9658796bb9f0\Infection\Metrics\Collector;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
final class MutationTestingResultsCollectorSubscriberFactory implements SubscriberFactory
{
    private array $collectors;
    public function __construct(Collector ...$collectors)
    {
        $this->collectors = $collectors;
    }
    public function create(OutputInterface $output) : EventSubscriber
    {
        return new MutationTestingResultsCollectorSubscriber(...$this->collectors);
    }
}
