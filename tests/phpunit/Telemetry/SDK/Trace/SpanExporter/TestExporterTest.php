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

namespace Infection\Tests\Telemetry\SDK\Trace\SpanExporter;

use Closure;
use Infection\Tests\TestingUtility\PHPUnit\ExpectsThrowables;
use LogicException;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\EventInterface;
use OpenTelemetry\SDK\Trace\LinkInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\StatusDataInterface;
use PHPUnit\Exception as PHPUnitException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(TestExporter::class)]
final class TestExporterTest extends TestCase
{
    use ExpectsThrowables;

    public function test_it_returns_the_exported_span_names(): void
    {
        $exporter = new TestExporter();
        $runSpan = self::createSpan('infection.run', true);
        $mutationAnalysisSpan = self::createSpan('infection.mutation_analysis', true);

        $exporter->export([
            $runSpan,
            $mutationAnalysisSpan,
        ]);

        Assert::assertSame(
            [
                'infection.run',
                'infection.mutation_analysis',
            ],
            $exporter->getSpanNames(),
        );
    }

    public function test_it_returns_the_exported_spans_by_name(): void
    {
        $exporter = new TestExporter();
        $runSpan = self::createSpan('infection.run', true);
        $firstMutationAnalysisSpan = self::createSpan('infection.mutation_analysis', true);
        $secondMutationAnalysisSpan = self::createSpan('infection.mutation_analysis', true);

        $exporter->export([
            $runSpan,
            $firstMutationAnalysisSpan,
            $secondMutationAnalysisSpan,
        ]);

        Assert::assertSame(
            [
                $firstMutationAnalysisSpan,
                $secondMutationAnalysisSpan,
            ],
            $exporter->getSpansByName('infection.mutation_analysis'),
        );
    }

    public function test_it_accepts_finished_spans(): void
    {
        $exporter = new TestExporter();
        $exporter->export([
            self::createSpan('infection.run', true),
        ]);

        $exporter->assertAllSpansAreFinished();
    }

    public function test_it_rejects_unfinished_spans(): void
    {
        $exporter = new TestExporter();
        $exporter->export([
            self::createSpan('infection.run', false),
        ]);

        $failure = $this->expectToThrow(
            self::rethrowPhpUnitFailures(static function () use ($exporter): void {
                $exporter->assertAllSpansAreFinished();
            }),
        );

        Assert::assertStringStartsWith(
            'Expected the span "infection.run" to have ended.',
            $failure->getMessage(),
        );
    }

    private static function createSpan(string $name, bool $ended): SpanDataInterface
    {
        return new class($name, $ended) implements SpanDataInterface {
            public function __construct(
                private readonly string $name,
                private readonly bool $ended,
            ) {
            }

            public function getName(): string
            {
                return $this->name;
            }

            public function hasEnded(): bool
            {
                return $this->ended;
            }

            public function getKind(): int
            {
                throw new LogicException('Not implemented.');
            }

            public function getContext(): SpanContextInterface
            {
                throw new LogicException('Not implemented.');
            }

            public function getParentContext(): SpanContextInterface
            {
                throw new LogicException('Not implemented.');
            }

            public function getTraceId(): string
            {
                throw new LogicException('Not implemented.');
            }

            public function getSpanId(): string
            {
                throw new LogicException('Not implemented.');
            }

            public function getParentSpanId(): string
            {
                throw new LogicException('Not implemented.');
            }

            public function getStatus(): StatusDataInterface
            {
                throw new LogicException('Not implemented.');
            }

            public function getStartEpochNanos(): int
            {
                throw new LogicException('Not implemented.');
            }

            public function getAttributes(): AttributesInterface
            {
                throw new LogicException('Not implemented.');
            }

            /**
             * @return list<EventInterface>
             */
            public function getEvents(): array
            {
                throw new LogicException('Not implemented.');
            }

            /**
             * @return list<LinkInterface>
             */
            public function getLinks(): array
            {
                throw new LogicException('Not implemented.');
            }

            public function getEndEpochNanos(): int
            {
                throw new LogicException('Not implemented.');
            }

            public function getInstrumentationScope(): InstrumentationScopeInterface
            {
                throw new LogicException('Not implemented.');
            }

            public function getResource(): ResourceInfo
            {
                throw new LogicException('Not implemented.');
            }

            public function getTotalDroppedEvents(): int
            {
                throw new LogicException('Not implemented.');
            }

            public function getTotalDroppedLinks(): int
            {
                throw new LogicException('Not implemented.');
            }
        };
    }

    /**
     * @param Closure(): void $action
     *
     * @return Closure(): void
     */
    private static function rethrowPhpUnitFailures(Closure $action): Closure
    {
        return static function () use ($action): void {
            try {
                $action();
            } catch (PHPUnitException $error) {
                throw new RuntimeException(
                    $error->getMessage(),
                    previous: $error,
                );
            }
        };
    }
}
