<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event\Subscriber;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
final class MutationGeneratingConsoleLoggerSubscriberFactory implements SubscriberFactory
{
    public function __construct(private bool $skipProgressBar)
    {
    }
    public function create(OutputInterface $output) : EventSubscriber
    {
        return $this->skipProgressBar ? new CiMutationGeneratingConsoleLoggerSubscriber($output) : new MutationGeneratingConsoleLoggerSubscriber($output);
    }
}
