<?php

declare(strict_types=1);

namespace Infection\Telemetry\Reporter;

use Infection\Console\IO;
use Infection\Resource\Memory\MemoryFormatter;
use Infection\Resource\Time\TimeFormatter;
use Infection\Telemetry\Tracing\Trace;
use Symfony\Component\Console\Output\OutputInterface;
use function memory_get_peak_usage;
use function sprintf;

final readonly class ConsoleReporter implements TraceReporter
{
    public function __construct(
        private TimeFormatter $timeFormatter,
        private MemoryFormatter $memoryFormatter,
        private IO $io,
    ) {
    }

    public function report(Trace $trace): void
    {
        $this->io->writeln([
            '',
            sprintf(
                'Time: %s. Memory: %s. Threads: %s',
                $this->timeFormatter->toHumanReadableString(
                    $trace->spans,
                ),
                $this->memoryFormatter->toHumanReadableString(memory_get_peak_usage(true)),
            ),
        ]);
    }
}
