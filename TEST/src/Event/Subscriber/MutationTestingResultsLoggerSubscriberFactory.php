<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event\Subscriber;

use _HumbugBox9658796bb9f0\Infection\Logger\MutationTestingResultsLogger;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
final class MutationTestingResultsLoggerSubscriberFactory implements SubscriberFactory
{
    public function __construct(private MutationTestingResultsLogger $logger)
    {
    }
    public function create(OutputInterface $output) : EventSubscriber
    {
        return new MutationTestingResultsLoggerSubscriber($this->logger);
    }
}
