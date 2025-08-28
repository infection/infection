<?php

declare(strict_types=1);

namespace Infection\Telemetry\Reporter;

use Infection\Console\IO;
use Infection\Resource\Memory\MemoryFormatter;
use Infection\Resource\Time\TimeFormatter;
use Infection\Telemetry\Tracing\Span;
use Infection\Telemetry\Tracing\Trace;
use Symfony\Component\Console\Output\OutputInterface;
use function count;
use function memory_get_peak_usage;
use function sprintf;
use function str_repeat;

final readonly class ConsoleReporter implements TraceReporter
{
    private const INDENT = '  ';

    public function __construct(
        private TimeFormatter $timeFormatter,
        private MemoryFormatter $memoryFormatter,
        private IO $io,
    ) {
    }

    public function report(Trace $trace): void
    {
        $this->io->newLine();
        $this->io->writeln(
            sprintf('Trace ID: <comment>%s</comment>', $trace->id),
        );
        $this->io->newLine();

        foreach ($trace->spans as $span) {
            $this->printSpan($span);
        }

//        $totalDuration = $this->calculateTotalDuration($trace->spans);
//
//        $this->io->writeln([
//            '',
//            sprintf(
//                'Time: %s. Memory: %s.',
//                $this->timeFormatter->toHumanReadableString($totalDuration),
//                $this->memoryFormatter->toHumanReadableString(memory_get_peak_usage(true)),
//            ),
//        ]);
    }

    private function printSpan(Span $span, int $depth = 0): void
    {
        $indent = str_repeat(self::INDENT, $depth);
        //$duration = $this->calculateSpanDuration($span);
        
        $this->io->writeln(
            sprintf(
                '%s- %s',
                //'%s- %s (%s; %s)',
                $indent,
                $span->id,
//                $this->timeFormatter->toHumanReadableString(
//                    $span->duration,
//                ),
//                $this->memoryFormatter->toHumanReadableString($duration),
            ),
            OutputInterface::VERBOSITY_NORMAL
        );

        foreach ($span->children as $child) {
            $this->printSpan($child, $depth + 1);
        }
    }

    private function calculateSpanDuration(Span $span): float
    {
        $duration = $span->end->time->getDuration($span->start->time);
        
        return $duration->seconds + ($duration->nanoseconds / 1_000_000_000);
    }

    /**
     * @param list<Span> $spans
     */
    private function calculateTotalDuration(array $spans): float
    {
        $totalDuration = 0.0;
        
        foreach ($spans as $span) {
            $duration = $this->calculateSpanDuration($span);
            $totalDuration = max($totalDuration, $duration);
        }
        
        return $totalDuration;
    }
}
