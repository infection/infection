<?php

declare(strict_types=1);

namespace Infection\Telemetry\Reporter;

use Infection\Console\IO;
use Infection\Resource\Memory\MemoryFormatter;
use Infection\Resource\Time\TimeFormatter;
use Infection\Telemetry\Metric\Time\DurationFormatter;
use Infection\Telemetry\Tracing\RootScopes;
use Infection\Telemetry\Tracing\Span;
use Infection\Telemetry\Tracing\Trace;
use Symfony\Component\Console\Output\OutputInterface;
use function array_filter;
use function array_map;
use function array_values;
use function count;
use function in_array;
use function memory_get_peak_usage;
use function sprintf;
use function str_repeat;

final readonly class ConsoleReporter
{
    private const FILE_SCOPE = 'file';

    private BoxDrawer $boxDrawer;

    public function __construct(
        private DurationFormatter $durationFormatter,
        private MemoryFormatter   $memoryFormatter,
        private IO                $io,
    ) {
        $this->boxDrawer = new BoxDrawer();
    }

    /**
     * @param positive-int $maxDepth
     * @param list<RootScopes> $rootScopes
     */
    public function report(
        Trace $trace,
        int   $maxDepth,
        array  $rootScopes,
    ): void
    {
        $this->io->newLine();
        $this->io->writeln(
            sprintf('Trace ID: <comment>%s</comment>', $trace->id),
        );
        $this->io->newLine();

        $filteredTrace = self::filterSpansByScope($trace, $rootScopes);

        $spansCount = count($filteredTrace->spans);

        foreach ($filteredTrace->spans as $index => $span) {
            $this->printSpan(
                $span,
                $maxDepth,
                parent: $filteredTrace,
                isLast: $index === $spansCount - 1,
            );
        }
    }

    /**
     * @param list<RootScopes> $rootScopes
     */
    private static function filterSpansByScope(
        Trace $trace,
        array $rootScopes,
    ): Trace
    {
        $rootScopeValues = array_map(
            static fn (RootScopes $scope) => $scope->value,
            $rootScopes,
        );

        $filter = static fn (Span $span) => in_array($span->scope, $rootScopeValues, true);

        $filteredSpans = array_filter(
            $trace->spans,
            $filter,
        );

        return $trace->withSpans(
            array_values($filteredSpans),
        );
    }

    /**
     * @param positive-int $maxDepth
     */
    private function printSpan(
        Span $span,
        int $maxDepth,
        Trace|Span $parent,
        int $depth = 0,
        bool $isLast = false,
    ): void
    {
        $childrenCount = count($span->children);
        $displayChildren = $childrenCount === 0 || $depth < $maxDepth;

        self::printSpanLabel(
            $span,
            $depth,
            $isLast,
            $parent,
            $displayChildren,
            $childrenCount,
        );

        if (!$displayChildren) {
            return;
        }

        foreach ($span->children as $index => $child) {
            $this->printSpan(
                $child,
                $maxDepth,
                parent: $span,
                depth: $depth + 1,
                isLast: $index === $childrenCount - 1,
            );
        }
    }

    /**
     * @param positive-int $childrenCount
     */
    private function printSpanLabel(
        Span $span,
        int $depth,
        bool $isLast,
        Trace|Span $parent,
        bool $displayChildren,
        int $childrenCount,
    ): void
    {
        $this->io->writeln(
            sprintf(
                '%s- %s - %s (%s%%), peak %s, Δ%s%s',
                $this->boxDrawer->draw($depth, $isLast),
                $span->id,
                $this->durationFormatter->toHumanReadableString(
                    $span->getDuration(),
                ),
                $span->getDurationPercentage(
                    $parent->getDuration(),
                ),
                $this->memoryFormatter->toHumanReadableString(
                    $span->end->peakMemoryUsage,
                ),
                $this->memoryFormatter->toHumanReadableString(
                    $span->getMemoryUsage(),
                ),
                $displayChildren
                    ? ''
                    : self::getHiddenChildrenLabel($childrenCount),
            ),
            OutputInterface::VERBOSITY_NORMAL
        );
    }

    /**
     * @param positive-int $count
     */
    private static function getHiddenChildrenLabel(int $count): string
    {
        return sprintf(
            ' [+%d %s]',
            $count,
            self::getChildrenText($count),
        );
    }

    /**
     * @param positive-int $count
     */
    private static function getChildrenText(int $count): string
    {
        return $count > 1 ? 'children' : 'child';
    }
}
