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

namespace Infection\Telemetry\Reporter\Html;

use function array_map;
use function count;
use function current;
use const ENT_HTML5;
use const ENT_QUOTES;
use function htmlspecialchars;
use Infection\Console\IO;
use Infection\FileSystem\FileSystem;
use Infection\Resource\Memory\MemoryFormatter;
use Infection\Telemetry\Metric\Time\Duration;
use Infection\Telemetry\Metric\Time\DurationFormatter;
use Infection\Telemetry\Metric\Time\HRTime;
use Infection\Telemetry\Tracing\RootScope;
use Infection\Telemetry\Tracing\Span;
use Infection\Telemetry\Tracing\Trace;
use function json_encode;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use function sprintf;
use stdClass;
use function str_replace;

final readonly class HtmlReporter
{
    public function __construct(
        private DurationFormatter $durationFormatter,
        private MemoryFormatter $memoryFormatter,
        private IO $io,
        private FileSystem $fileSystem,
        private string $outputPath,
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
        $html = $this->generateHtml($trace);

        $this->fileSystem->dumpFile($this->outputPath, $html);

        $this->io->writeln(
            sprintf('HTML trace written to: <info>%s</info>', $this->outputPath),
        );
    }

    private function generateHtml(Trace $trace): string
    {
        $traceData = $this->generateTraceData($trace);

        $traceDataJson = json_encode(
            $traceData,
            JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );

        return $this->renderHtml($traceDataJson);
    }

    /**
     * @return stdClass|mixed[]
     */
    private function generateTraceData(Trace $trace): array
    {
        /** @var Span $firstSpan */
        $firstSpan = current($trace->spans);
        $traceStart = $firstSpan->start->time;
        $totalDuration = $trace->getDuration();

        return [
            'id' => htmlspecialchars($trace->id, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'duration' => $this->durationFormatter->toHumanReadableString($totalDuration),
            'durationInSeconds' => $totalDuration->seconds,
            'spanCount' => $this->countAllSpans($trace->spans),
            'spans' => array_map(
                fn (Span $span): array => $this->buildSpanData($span, $traceStart, $totalDuration),
                $trace->spans,
            ),
        ];
    }

    /**
     * @param list<Span> $spans
     */
    private function countAllSpans(array $spans): int
    {
        $count = count($spans);

        foreach ($spans as $span) {
            $count += $this->countAllSpans($span->children);
        }

        return $count;
    }

    /**
     * @return array{
     *   id: string,
     *   scope: string,
     *   startOffset: float,
     *   duration: float,
     *   durationPct: int,
     *   startPct: float,
     *   widthPct: float,
     *   peakMemory: string,
     *   memoryDelta: string,
     *   durationLabel: string,
     *   children: list<array>
     * }
     */
    private function buildSpanData(Span $span, HRTime $traceStart, Duration $total): array
    {
        $startOffset = $span->start->time->getDuration($traceStart)->toSeconds();
        $duration = $span->getDuration()->toSeconds();
        $totalSeconds = $total->toSeconds();

        $startPercentage = $totalSeconds > 0.0 ? ($startOffset / $totalSeconds) * 100.0 : 0.0;
        $widthPercentage = $totalSeconds > 0.0 ? ($duration / $totalSeconds) * 100.0 : 0.0;
        $durationPercentage = $span->getDurationPercentage($total);

        return [
            'id' => (string) $span->id,
            'scopeKey' => $span->scope->value,
            'scope' => ScopeName::getName($span->scope),
            'startOffset' => $startOffset,
            'duration' => $duration,
            'durationPercentage' => $durationPercentage,
            'startPercentage' => $startPercentage,
            'widthPercentage' => $widthPercentage,
            'peakMemory' => $this->memoryFormatter->toHumanReadableString($span->end->peakMemoryUsage),
            'memoryDelta' => $this->memoryFormatter->toHumanReadableString($span->getMemoryUsage()),
            'durationLabel' => $this->durationFormatter->toHumanReadableString($span->getDuration()),
            'attributes' => $span->attributes,
            'children' => array_map(
                fn (Span $child): array => $this->buildSpanData($child, $traceStart, $total),
                $span->children,
            ),
        ];
    }

    private function renderHtml(
        string $traceDataJson,
    ): string {
        $template = $this->fileSystem->readFile(
            __DIR__ . '/trace.html.template',
        );

        return str_replace(
            '__TRACE_DATA_JSON__',
            $traceDataJson,
            $template,
        );
    }
}
