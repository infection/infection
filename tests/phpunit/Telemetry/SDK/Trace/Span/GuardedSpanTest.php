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
use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\Context;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(GuardedSpan::class)]
final class GuardedSpanTest extends TestCase
{
    public function test_it_tracks_whether_the_span_is_open(): void
    {
        $span = $this->createSpan('infection.run');

        $this->assertSame(
            'infection.run',
            $span->getName(),
        );
        $this->assertTrue($span->isOpen());

        $context = $span->storeInContext(Context::getCurrent());

        $this->assertSame($span, Span::fromContext($context));

        $span->end(20);

        $this->assertFalse($span->isOpen());
    }

    public function test_it_fails_when_readable_span_data_is_requested_for_a_non_readable_span(): void
    {
        $span = $this->createSpan('infection.invalid');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Span "infection.invalid" does not expose readable span data.');

        $span->toSpanData();
    }

    private function createSpan(string $name): GuardedSpan
    {
        return (new GuardedTracer(NoopTracer::getInstance()))->guardStartedSpan(
            span: Span::getInvalid(),
            name: $name,
            parent: null,
        );
    }
}
