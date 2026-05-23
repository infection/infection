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

namespace Infection\Tests\Telemetry\SDK\Trace\Tracer;

use function array_keys;
use function array_map;
use function implode;
use Infection\Tests\Telemetry\SDK\Trace\Span\GuardedSpan;
use Infection\Tests\Telemetry\SDK\Trace\Span\GuardedSpanBuilder;
use OpenTelemetry\API\Trace\SpanBuilderInterface;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\TracerInterface;
use function spl_object_id;
use function sprintf;
use Webmozart\Assert\Assert;

/**
 * Test tracer decorator that tracks started spans and fails when spans are
 * leaked, ended twice, or ended before their child spans.
 *
 * @internal
 */
final class GuardedTracer implements TracerInterface
{
    /** @var array<int, GuardedSpan> */
    private array $openSpans = [];

    /** @var array<int, array<int, true>> */
    private array $openChildrenByParentId = [];

    /** @var array<int, int> */
    private array $parentIdsByChildId = [];

    public function __construct(
        private readonly TracerInterface $decoratedTracer,
    ) {
    }

    public function spanBuilder(string $spanName): SpanBuilderInterface
    {
        return new GuardedSpanBuilder(
            tracer: $this,
            decoratedBuilder: $this->decoratedTracer->spanBuilder($spanName),
            spanName: $spanName,
        );
    }

    public function isEnabled(): bool
    {
        return $this->decoratedTracer->isEnabled();
    }

    public function assertHasNoOpenSpans(): void
    {
        if ($this->openSpans === []) {
            return;
        }

        Assert::same(
            $this->openSpans,
            [],
            sprintf(
                "Expected all spans to be closed, but the following spans are still open:\n%s",
                implode(
                    "\n",
                    array_map(
                        static fn (GuardedSpan $span): string => '- ' . $span->getName(),
                        $this->openSpans,
                    ),
                ),
            ),
        );
    }

    public function guardStartedSpan(
        SpanInterface $span,
        string $name,
        ?GuardedSpan $parent,
    ): GuardedSpan {
        if ($parent !== null) {
            self::assertParentSpanIsOpen($name, $parent);
        }

        $guardedSpan = new GuardedSpan($this, $span, $name);
        $spanId = spl_object_id($guardedSpan);

        $this->openSpans[$spanId] = $guardedSpan;

        if ($parent !== null) {
            $parentId = spl_object_id($parent);

            $this->openChildrenByParentId[$parentId][$spanId] = true;
            $this->parentIdsByChildId[$spanId] = $parentId;
        }

        return $guardedSpan;
    }

    public function guardEndingSpan(GuardedSpan $span): void
    {
        $spanId = spl_object_id($span);

        $this->assertSpanIsOpen($spanId, $span);

        $this->assertHasNoOpenedChildSpan($spanId, $span);

        unset($this->openSpans[$spanId]);

        $parentId = $this->parentIdsByChildId[$spanId] ?? null;

        if ($parentId !== null) {
            unset($this->openChildrenByParentId[$parentId][$spanId]);
            unset($this->parentIdsByChildId[$spanId]);
        }
    }

    private static function assertParentSpanIsOpen(
        string $name,
        GuardedSpan $parent,
    ): void {
        Assert::true(
            $parent->isOpen(),
            sprintf(
                'Cannot start span "%s" as child of already closed span "%s".',
                $name,
                $parent->getName(),
            ),
        );
    }

    private function assertSpanIsOpen(int $spanId, GuardedSpan $span): void
    {
        Assert::keyExists(
            $this->openSpans,
            $spanId,
            sprintf(
                'Span "%s" was ended more than once.',
                $span->getName(),
            ),
        );
    }

    private function assertHasNoOpenedChildSpan(int $spanId, GuardedSpan $span): void
    {
        $openChildIds = array_keys($this->openChildrenByParentId[$spanId] ?? []);

        Assert::same(
            $openChildIds,
            [],
            sprintf(
                "Cannot end span \"%s\" while the following child spans are still open:\n%s",
                $span->getName(),
                implode(
                    "\n",
                    array_map(
                        fn (int $childId): string => '- ' . $this->openSpans[$childId]->getName(),
                        $openChildIds,
                    ),
                ),
            ),
        );
    }
}
