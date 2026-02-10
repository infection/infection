<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Telemetry\Reporter;

use function array_filter;
use function array_map;
use function array_values;
use function count;
use function in_array;
use Infection\Console\IO;
use Infection\Resource\Memory\MemoryFormatter;
use Infection\Telemetry\Metric\Time\DurationFormatter;
use Infection\Telemetry\Tracing\RootScope;
use Infection\Telemetry\Tracing\Span;
use Infection\Telemetry\Tracing\Trace;
use InvalidArgumentException;
use function sprintf;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsoleReporter
{
    private BoxDrawer $boxDrawer;

    /**
     * @var positive-int|0
     */
    private int $filteredCount = 0;

    /**
     * @var array{positive-int, bool}|null
     */
    private ?array $lastFiltered = [];

    public function __construct(
        private readonly DurationFormatter $durationFormatter,
        private readonly MemoryFormatter $memoryFormatter,
        private readonly IO $io,
    ) {
    }

    /**
     * @param positive-int $maxDepth
     * @param list<RootScope> $rootScopes
     * @param int<0, 100> $minTimeThreshold
     */
    public function report(
        Trace $trace,
        int $maxDepth,
        array $rootScopes,
        int $minTimeThreshold,
        ?string $spanId,
    ): void {
        $this->boxDrawer = new BoxDrawer();
        $this->resetLastFiltered();

        $this->io->newLine();
        $this->io->writeln(
            sprintf('Trace ID: <comment>%s</comment>', $trace->id),
        );
        $this->io->newLine();

        $filteredTrace = self::filterSpans(
            $trace,
            $rootScopes,
            $spanId,
        );

        $spansCount = count($filteredTrace->spans);

        foreach ($filteredTrace->spans as $index => $span) {
            $this->printSpan(
                $span,
                $maxDepth,
                parent: $filteredTrace,
                minTimeThreshold: $minTimeThreshold,
                isLast: $index === $spansCount - 1,
            );
        }
    }

    /**
     * @param positive-int|0 $filteredCount
     * @param positive-int|0 $lastFilteredDepth
     * @param bool $lastFilteredIsLast
     */
    public function printPreviouslyFilteredSpans(
        int $filteredCount,
        int $lastFilteredDepth,
        mixed $lastFilteredIsLast,
    ): void {
        $this->io->writeln(
            sprintf(
                '%s filtered%s',
                $this->boxDrawer->draw($lastFilteredDepth, $lastFilteredIsLast),
                $filteredCount === 1
                    ? ''
                    : ' (x' . $filteredCount . ')',
            ),
        );

        $this->resetLastFiltered();
    }

    /**
     * @param list<RootScope> $rootScopes
     */
    private static function filterSpans(
        Trace $trace,
        array $rootScopes,
        ?string $spanId,
    ): Trace {
        $rootScopeValues = array_map(
            static fn (RootScope $scope) => $scope->value,
            $rootScopes,
        );

        $filter = static fn (Span $span) => in_array($span->scope, $rootScopeValues, true);

        $filteredSpans = array_filter(
            $trace->spans,
            $filter,
        );

        if ($spanId !== null) {
            $filteredSpans = [self::findSpanById($spanId, $filteredSpans)];
        }

        return $trace->withSpans(
            array_values($filteredSpans),
        );
    }

    private static function findSpanById(string $id, array $spans): Span
    {
        $span = self::findSpanByIdRecursively($id, $spans);

        if ($span === null) {
            throw new InvalidArgumentException(
                sprintf(
                    'Not span with the ID "%s" was found.',
                    $id,
                ),
            );
        }

        return $span;
    }

    /**
     * @param Span[] $spans
     */
    private static function findSpanByIdRecursively(string $id, array $spans): ?Span
    {
        if (count($spans) === 0) {
            return null;
        }

        foreach ($spans as $span) {
            if ($span->id === $id) {
                return $span;
            }

            $result = self::findSpanByIdRecursively($id, $span->children);

            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    /**
     * @param positive-int $maxDepth
     * @param int<0,100> $minTimeThreshold
     */
    private function printSpan(
        Span $span,
        int $maxDepth,
        Trace|Span $parent,
        int $minTimeThreshold,
        int $depth = 0,
        bool $isLast = false,
    ): void {
        $childrenCount = count($span->children);
        $displayChildren = $childrenCount === 0 || $depth < $maxDepth;

        $durationPercentage = $span->getDurationPercentage(
            $parent->getDuration(),
        );

        if ($durationPercentage < $minTimeThreshold) {
            if ($this->filteredCount > 0) {
                [$lastFilteredDepth, $lastFilteredIsLast] = $this->lastFiltered;

                if ($lastFilteredDepth !== $depth) {
                    $this->printPreviouslyFilteredSpans(
                        $this->filteredCount,
                        $lastFilteredDepth,
                        $lastFilteredIsLast,
                    );
                }
            }

            ++$this->filteredCount;
            $this->lastFiltered = [$depth, $isLast];

            return;
        }

        if ($this->filteredCount > 0) {
            [$lastFilteredDepth, $lastFilteredIsLast] = $this->lastFiltered;

            $this->printPreviouslyFilteredSpans(
                $this->filteredCount,
                $lastFilteredDepth,
                $lastFilteredIsLast,
            );
        }

        self::printSpanLabel(
            $span,
            $depth,
            $isLast,
            $displayChildren,
            $childrenCount,
            $durationPercentage,
        );

        if (!$displayChildren) {
            return;
        }

        foreach ($span->children as $index => $child) {
            $this->printSpan(
                $child,
                $maxDepth,
                parent: $span,
                minTimeThreshold: $minTimeThreshold,
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
        bool $displayChildren,
        int $childrenCount,
        int $durationPercentage,
    ): void {
        $this->io->writeln(
            sprintf(
                '%s %s - %s (%s%%), peak %s, Δ%s%s',
                $this->boxDrawer->draw($depth, $isLast),
                $span->id,
                $this->durationFormatter->toHumanReadableString(
                    $span->getDuration(),
                ),
                $durationPercentage,
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
            OutputInterface::VERBOSITY_NORMAL,
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

    private function resetLastFiltered(): void
    {
        $this->filteredCount = 0;
        $this->lastFiltered = null;
    }
}
