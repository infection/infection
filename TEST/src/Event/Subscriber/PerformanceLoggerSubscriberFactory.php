<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event\Subscriber;

use _HumbugBox9658796bb9f0\Infection\Resource\Listener\PerformanceLoggerSubscriber;
use _HumbugBox9658796bb9f0\Infection\Resource\Memory\MemoryFormatter;
use _HumbugBox9658796bb9f0\Infection\Resource\Time\Stopwatch;
use _HumbugBox9658796bb9f0\Infection\Resource\Time\TimeFormatter;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
final class PerformanceLoggerSubscriberFactory implements SubscriberFactory
{
    public function __construct(private Stopwatch $stopwatch, private TimeFormatter $timeFormatter, private MemoryFormatter $memoryFormatter)
    {
    }
    public function create(OutputInterface $output) : EventSubscriber
    {
        return new PerformanceLoggerSubscriber($this->stopwatch, $this->timeFormatter, $this->memoryFormatter, $output);
    }
}
