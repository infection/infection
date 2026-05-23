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

namespace Infection\Tests\Telemetry\SDK\Trace\Span;

use Infection\Tests\Telemetry\SDK\Trace\Tracer\GuardedTracer;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanBuilderInterface;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\Context\ContextInterface;

/**
 * Span builder decorator that keeps delegating SDK span configuration while
 * registering every created span with the guarded tracer.
 *
 * @internal
 */
final class GuardedSpanBuilder implements SpanBuilderInterface
{
    private ContextInterface|false|null $parent = null;

    public function __construct(
        private readonly GuardedTracer $tracer,
        private readonly SpanBuilderInterface $decoratedBuilder,
        private readonly string $spanName,
    ) {
    }

    public function setParent(ContextInterface|false|null $context): SpanBuilderInterface
    {
        $this->parent = $context;
        $this->decoratedBuilder->setParent($context);

        return $this;
    }

    public function addLink(SpanContextInterface $context, iterable $attributes = []): SpanBuilderInterface
    {
        $this->decoratedBuilder->addLink($context, $attributes);

        return $this;
    }

    public function setAttribute(string $key, mixed $value): SpanBuilderInterface
    {
        $this->decoratedBuilder->setAttribute($key, $value);

        return $this;
    }

    public function setAttributes(iterable $attributes): SpanBuilderInterface
    {
        $this->decoratedBuilder->setAttributes($attributes);

        return $this;
    }

    public function setStartTimestamp(int $timestampNanos): SpanBuilderInterface
    {
        $this->decoratedBuilder->setStartTimestamp($timestampNanos);

        return $this;
    }

    public function setSpanKind(int $spanKind): SpanBuilderInterface
    {
        $this->decoratedBuilder->setSpanKind($spanKind);

        return $this;
    }

    public function startSpan(): SpanInterface
    {
        $parentSpan = $this->parent instanceof ContextInterface
            ? Span::fromContext($this->parent)
            : null;

        return $this->tracer->guardStartedSpan(
            span: $this->decoratedBuilder->startSpan(),
            name: $this->spanName,
            parent: $parentSpan instanceof GuardedSpan
                ? $parentSpan
                : null,
        );
    }
}
