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
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ContextKeys;
use OpenTelemetry\Context\ScopeInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use function sprintf;
use Throwable;
use Webmozart\Assert\Assert;

/**
 * Span decorator that preserves the OpenTelemetry span APIs while notifying
 * the guarded tracer when the span is ended.
 *
 * @internal
 */
final class GuardedSpan implements ReadableSpanInterface, SpanInterface
{
    private bool $open = true;

    public function __construct(
        private readonly GuardedTracer $tracer,
        private readonly SpanInterface $decoratedSpan,
        private readonly string $name,
    ) {
    }

    public static function fromContext(ContextInterface $context): SpanInterface
    {
        return Span::fromContext($context);
    }

    public static function getCurrent(): SpanInterface
    {
        return Span::getCurrent();
    }

    public static function getInvalid(): SpanInterface
    {
        return Span::getInvalid();
    }

    public static function wrap(SpanContextInterface $spanContext): SpanInterface
    {
        return Span::wrap($spanContext);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParentContext(): SpanContextInterface
    {
        return $this->readableSpan()->getParentContext();
    }

    public function getInstrumentationScope(): InstrumentationScopeInterface
    {
        return $this->readableSpan()->getInstrumentationScope();
    }

    public function hasEnded(): bool
    {
        return $this->readableSpan()->hasEnded();
    }

    public function toSpanData(): SpanDataInterface
    {
        return $this->readableSpan()->toSpanData();
    }

    public function getDuration(): int
    {
        return $this->readableSpan()->getDuration();
    }

    public function getKind(): int
    {
        return $this->readableSpan()->getKind();
    }

    public function getAttribute(string $key): mixed
    {
        return $this->readableSpan()->getAttribute($key);
    }

    public function isOpen(): bool
    {
        return $this->open;
    }

    public function activate(): ScopeInterface
    {
        return $this->storeInContext(Context::getCurrent())->activate();
    }

    public function storeInContext(ContextInterface $context): ContextInterface
    {
        return $context->with(ContextKeys::span(), $this);
    }

    public function getContext(): SpanContextInterface
    {
        return $this->decoratedSpan->getContext();
    }

    public function isRecording(): bool
    {
        return $this->decoratedSpan->isRecording();
    }

    public function setAttribute(string $key, bool|int|float|string|array|null $value): SpanInterface
    {
        $this->decoratedSpan->setAttribute($key, $value);

        return $this;
    }

    public function setAttributes(iterable $attributes): SpanInterface
    {
        $this->decoratedSpan->setAttributes($attributes);

        return $this;
    }

    public function addLink(SpanContextInterface $context, iterable $attributes = []): SpanInterface
    {
        $this->decoratedSpan->addLink($context, $attributes);

        return $this;
    }

    public function addEvent(string $name, iterable $attributes = [], ?int $timestamp = null): SpanInterface
    {
        $this->decoratedSpan->addEvent($name, $attributes, $timestamp);

        return $this;
    }

    public function recordException(Throwable $exception, iterable $attributes = []): SpanInterface
    {
        $this->decoratedSpan->recordException($exception, $attributes);

        return $this;
    }

    public function updateName(string $name): SpanInterface
    {
        $this->decoratedSpan->updateName($name);

        return $this;
    }

    public function setStatus(string $code, ?string $description = null): SpanInterface
    {
        $this->decoratedSpan->setStatus($code, $description);

        return $this;
    }

    public function end(?int $endEpochNanos = null): void
    {
        $this->tracer->guardEndingSpan($this);
        $this->decoratedSpan->end($endEpochNanos);
        $this->open = false;
    }

    private function readableSpan(): ReadableSpanInterface
    {
        Assert::isInstanceOf(
            $this->decoratedSpan,
            ReadableSpanInterface::class,
            sprintf(
                'Span "%s" does not expose readable span data.',
                $this->name,
            ),
        );

        return $this->decoratedSpan;
    }
}
