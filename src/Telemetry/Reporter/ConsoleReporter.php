<?php

declare(strict_types=1);

namespace Infection\Telemetry\Reporter;

use Infection\Console\IO;
use Infection\Resource\Memory\MemoryFormatter;
use Infection\Resource\Time\TimeFormatter;
use Infection\Telemetry\Metric\Time\DurationFormatter;
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

    private BoxDrawer $boxDrawer;

    public function __construct(
        private DurationFormatter $durationFormatter,
        private MemoryFormatter   $memoryFormatter,
        private IO                $io,
    ) {
        $this->boxDrawer = new BoxDrawer();
    }

    public function report(Trace $trace): void
    {
        $this->io->newLine();
        $this->io->writeln(
            sprintf('Trace ID: <comment>%s</comment>', $trace->id),
        );
        $this->io->newLine();

        $spansCount = count($trace->spans);

        foreach ($trace->spans as $index => $span) {
            $this->printSpan(
                $span,
                isLast: $index === $spansCount - 1,
            );
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

    private function printSpan(
        Span $span,
        int $depth = 0,
        bool $isLast = false,
    ): void
    {
        $indent = str_repeat(self::INDENT, $depth);
        //$duration = $this->calculateSpanDuration($span);

        $this->io->writeln(
            sprintf(
                '%s- %s (%s, peak %s, Δ%s)',
                $this->boxDrawer->draw($depth, $isLast),
                $span->id,
                $this->durationFormatter->toHumanReadableString(
                    $span->getDuration(),
                ),
                $this->memoryFormatter->toHumanReadableString(
                    $span->end->peakMemoryUsage,
                ),
                $this->memoryFormatter->toHumanReadableString(
                    $span->getMemoryUsage(),
                ),
            ),
            OutputInterface::VERBOSITY_NORMAL
        );

        $childrenCount = count($span->children);

        foreach ($span->children as $index => $child) {
            $this->printSpan(
                $child,
                depth: $depth + 1,
                isLast: $index === $childrenCount - 1,
            );
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
