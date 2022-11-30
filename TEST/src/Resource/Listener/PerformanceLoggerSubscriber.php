<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Resource\Listener;

use _HumbugBox9658796bb9f0\Infection\Event\ApplicationExecutionWasFinished;
use _HumbugBox9658796bb9f0\Infection\Event\ApplicationExecutionWasStarted;
use _HumbugBox9658796bb9f0\Infection\Event\Subscriber\EventSubscriber;
use _HumbugBox9658796bb9f0\Infection\Resource\Memory\MemoryFormatter;
use _HumbugBox9658796bb9f0\Infection\Resource\Time\Stopwatch;
use _HumbugBox9658796bb9f0\Infection\Resource\Time\TimeFormatter;
use function memory_get_peak_usage;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
final class PerformanceLoggerSubscriber implements EventSubscriber
{
    public function __construct(private Stopwatch $stopwatch, private TimeFormatter $timeFormatter, private MemoryFormatter $memoryFormatter, private OutputInterface $output)
    {
    }
    public function onApplicationExecutionWasStarted(ApplicationExecutionWasStarted $event) : void
    {
        $this->stopwatch->start();
    }
    public function onApplicationExecutionWasFinished(ApplicationExecutionWasFinished $event) : void
    {
        $time = $this->stopwatch->stop();
        $this->output->writeln(['', sprintf('Time: %s. Memory: %s', $this->timeFormatter->toHumanReadableString($time), $this->memoryFormatter->toHumanReadableString(memory_get_peak_usage(\true)))]);
    }
}
