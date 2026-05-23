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

namespace Infection\Tests\Telemetry\Subscriber;

use function implode;
use Infection\CannotBeInstantiated;
use OpenTelemetry\API\Trace\SpanContextValidator;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use function sprintf;
use function str_repeat;
use function usort;

/**
 * @internal
 */
final class SpanTreeRenderer
{
    use CannotBeInstantiated;

    /**
     * @param SpanDataInterface[] $spans
     */
    public static function render(array $spans): string
    {
        $childrenByParentId = [];

        foreach ($spans as $span) {
            $childrenByParentId[$span->getParentSpanId()][] = $span;
        }

        $lines = [];
        $rootSpans = $childrenByParentId[SpanContextValidator::INVALID_SPAN] ?? [];

        self::sortSpans($rootSpans);

        foreach ($rootSpans as $rootSpan) {
            self::renderSpanTreeNode($rootSpan, $childrenByParentId, $lines);
        }

        return implode("\n", $lines);
    }

    /**
     * @param SpanDataInterface $childrenByParentId
     * @param list<string> $lines
     */
    private static function renderSpanTreeNode(
        SpanDataInterface $span,
        array $childrenByParentId,
        array &$lines,
        int $depth = 0,
    ): void {
        $lines[] = sprintf(
            '%s%s [%d, %d]',
            str_repeat('  ', $depth),
            $span->getName(),
            $span->getStartEpochNanos(),
            $span->getEndEpochNanos(),
        );

        $children = $childrenByParentId[$span->getSpanId()] ?? [];

        self::sortSpans($children);

        foreach ($children as $child) {
            self::renderSpanTreeNode($child, $childrenByParentId, $lines, $depth + 1);
        }
    }

    /**
     * @param list<SpanDataInterface> $spans
     */
    private static function sortSpans(array &$spans): void
    {
        usort(
            $spans,
            static fn (SpanDataInterface $left, SpanDataInterface $right): int => $left->getStartEpochNanos() <=> $right->getStartEpochNanos(),
        );
    }
}
