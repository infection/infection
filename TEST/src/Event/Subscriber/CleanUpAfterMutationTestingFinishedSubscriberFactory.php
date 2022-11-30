<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event\Subscriber;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Filesystem;
final class CleanUpAfterMutationTestingFinishedSubscriberFactory implements SubscriberFactory
{
    public function __construct(private bool $debug, private Filesystem $fileSystem, private string $tmpDir)
    {
    }
    public function create(OutputInterface $output) : EventSubscriber
    {
        return $this->debug ? new NullSubscriber() : new CleanUpAfterMutationTestingFinishedSubscriber($this->fileSystem, $this->tmpDir);
    }
}
