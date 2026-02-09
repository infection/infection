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

namespace Infection\Telemetry\Tracing;

use function array_map;
use function count;
use function end;
use Infection\Telemetry\Metric\Snapshot;
use LogicException;
use function sprintf;

final class SpanBuilder
{
    public readonly string $id;

    private Snapshot $end;

    /**
     * @var list<SpanBuilder>
     */
    private array $children = [];

    public function __construct(
        // The scope ID should be unique per scope, but the same ID can be
        // re-used across different scopes.
        private readonly string $scopeId,
        private readonly string $scope,
        private readonly Snapshot $start,
    ) {
        $this->id = $this->scope . ':' . $this->scopeId;
    }

    /** @internal Should only be used by the Tracer */
    public function finish(Snapshot $end): void
    {
        $this->assertSpanWasNotFinished();

        $this->end = $end;
    }

    public function addChild(self $span): void
    {
        $this->children[] = $span;
    }

    public function build(): Span
    {
        if (count($this->children) > 0) {
            $this->end ??= $this->getChildrenLastSnapshot();
        }

        $this->assertSpanWasFinished();

        return new Span(
            $this->id,
            $this->scopeId,
            $this->scope,
            $this->start,
            $this->end,
            array_map(
                static fn (SpanBuilder $child) => $child->build(),
                $this->children,
            ),
        );
    }

    private function assertSpanWasFinished(): void
    {
        if (!isset($this->end)) {
            throw new LogicException(
                sprintf(
                    'The span "%s" for the scope "%s" was never finished.',
                    $this->scopeId,
                    $this->scope,
                ),
            );
        }
    }

    private function assertSpanWasNotFinished(): void
    {
        if (isset($this->end)) {
            throw new LogicException(
                sprintf(
                    'The span "%s" for the scope "%s" has already finished.',
                    $this->scopeId,
                    $this->scope,
                ),
            );
        }
    }

    private function getChildrenLastSnapshot(): Snapshot
    {
        return end($this->children)->end;
    }
}
