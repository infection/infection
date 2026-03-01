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

namespace Infection\Tests\Telemetry\Tracing;

use Infection\Telemetry\Metric\GarbageCollection\GarbageCollectorStatus;
use Infection\Telemetry\Metric\Memory\MemoryUsage;
use Infection\Telemetry\Metric\Snapshot;
use Infection\Telemetry\Metric\Time\Duration;
use Infection\Telemetry\Metric\Time\HRTime;
use Infection\Telemetry\Tracing\RootScope;
use Infection\Telemetry\Tracing\Scope;
use Infection\Telemetry\Tracing\Span;
use Infection\Tests\Telemetry\Metric\SnapshotBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Span::class)]
final class SpanTest extends TestCase
{
    #[DataProvider('durationProvider')]
    public function test_it_can_calculate_duration(
        Snapshot $start,
        Snapshot $end,
        Duration $expectedDuration,
    ): void {
        $span = SpanBuilder::withRootTestData()
            ->withStart($start)
            ->withEnd($end)
            ->build();

        $actualDuration = $span->getDuration();

        $this->assertEquals($expectedDuration, $actualDuration);
    }

    #[DataProvider('memoryUsageProvider')]
    public function test_it_can_calculate_memory_usage(
        Snapshot $start,
        Snapshot $end,
        MemoryUsage $expectedMemoryUsage,
    ): void {
        $span = SpanBuilder::withRootTestData()
            ->withStart($start)
            ->withEnd($end)
            ->build();

        $actualMemoryUsage = $span->getMemoryUsage();

        $this->assertEquals($expectedMemoryUsage, $actualMemoryUsage);
    }

    #[DataProvider('durationPercentageProvider')]
    public function test_it_can_calculate_duration_percentage(
        Span $span,
        Duration $totalDuration,
        int $expectedPercentage,
    ): void {
        $actualPercentage = $span->getDurationPercentage($totalDuration);

        $this->assertSame($expectedPercentage, $actualPercentage);
    }

    public static function spanProvider(): iterable
    {
        $gcStatus = self::createGarbageCollectorStatus();
        $snapshot1 = self::createSnapshot(0, 0, 1000);
        $snapshot2 = self::createSnapshot(1, 0, 2000);

        yield 'root scope with no children' => [
            'span-id-1',
            'scope-id-1',
            RootScope::ARTEFACT_COLLECTION,
            $snapshot1,
            $snapshot2,
            [],
        ];

        yield 'nested scope with no children' => [
            'span-id-2',
            'scope-id-2',
            Scope::INITIAL_TESTS,
            $snapshot1,
            $snapshot2,
            [],
        ];

        yield 'root scope with children' => [
            'span-id-3',
            'scope-id-3',
            RootScope::MUTATION_ANALYSIS,
            $snapshot1,
            $snapshot2,
            ['child-1', 'child-2'],
        ];

        yield 'different root scope variant' => [
            'span-id-4',
            'scope-id-4',
            RootScope::SOURCE_FILE,
            $snapshot1,
            $snapshot2,
            [],
        ];

        yield 'different nested scope variant' => [
            'span-id-5',
            'scope-id-5',
            Scope::MUTATION_GENERATION,
            $snapshot1,
            $snapshot2,
            ['child-1'],
        ];
    }

    public static function durationProvider(): iterable
    {
        yield 'zero duration' => [
            self::createSnapshot(10, 0, 1000),
            self::createSnapshot(10, 0, 1000),
            Duration::fromSecondsAndNanoseconds(0, 0),
        ];

        yield 'one second duration' => [
            self::createSnapshot(10, 0, 1000),
            self::createSnapshot(11, 0, 1000),
            Duration::fromSecondsAndNanoseconds(1, 0),
        ];

        yield 'half second duration' => [
            self::createSnapshot(10, 0, 1000),
            self::createSnapshot(10, 500_000_000, 1000),
            Duration::fromSecondsAndNanoseconds(0, 500_000_000),
        ];

        yield 'multiple seconds with nanoseconds' => [
            self::createSnapshot(5, 250_000_000, 1000),
            self::createSnapshot(10, 750_000_000, 1000),
            Duration::fromSecondsAndNanoseconds(5, 500_000_000),
        ];

        yield 'nanosecond borrow from seconds' => [
            self::createSnapshot(10, 800_000_000, 1000),
            self::createSnapshot(11, 200_000_000, 1000),
            Duration::fromSecondsAndNanoseconds(0, 400_000_000),
        ];

        yield 'large duration' => [
            self::createSnapshot(0, 0, 1000),
            self::createSnapshot(3600, 0, 1000),
            Duration::fromSecondsAndNanoseconds(3600, 0),
        ];
    }

    public static function memoryUsageProvider(): iterable
    {
        yield 'no memory change' => [
            self::createSnapshot(0, 0, 1000),
            self::createSnapshot(1, 0, 1000),
            MemoryUsage::fromBytes(0),
        ];

        yield 'memory increased by 1KB' => [
            self::createSnapshot(0, 0, 1000),
            self::createSnapshot(1, 0, 2024),
            MemoryUsage::fromBytes(1024),
        ];

        yield 'memory increased by 1MB' => [
            self::createSnapshot(0, 0, 1_000_000),
            self::createSnapshot(1, 0, 2_048_576),
            MemoryUsage::fromBytes(1_048_576),
        ];

        yield 'memory decreased' => [
            self::createSnapshot(0, 0, 2000),
            self::createSnapshot(1, 0, 1000),
            MemoryUsage::fromBytes(-1000),
        ];

        yield 'large memory increase' => [
            self::createSnapshot(0, 0, 1_000_000),
            self::createSnapshot(1, 0, 100_000_000),
            MemoryUsage::fromBytes(99_000_000),
        ];
    }

    public static function durationPercentageProvider(): iterable
    {
        yield 'zero of zero is zero percent' => [
            self::createSpan(0, 0, 0, 0),
            Duration::fromSecondsAndNanoseconds(0, 0),
            0,
        ];

        yield 'any duration of zero total is zero percent' => [
            self::createSpan(0, 0, 10, 0),
            Duration::fromSecondsAndNanoseconds(0, 0),
            0,
        ];

        yield 'half of total is 50 percent' => [
            self::createSpan(0, 0, 5, 0),
            Duration::fromSecondsAndNanoseconds(10, 0),
            50,
        ];

        yield 'equal to total is 100 percent' => [
            self::createSpan(0, 0, 10, 0),
            Duration::fromSecondsAndNanoseconds(10, 0),
            100,
        ];

        yield 'one quarter is 25 percent' => [
            self::createSpan(0, 0, 1, 0),
            Duration::fromSecondsAndNanoseconds(4, 0),
            25,
        ];

        yield 'three quarters is 75 percent' => [
            self::createSpan(0, 0, 3, 0),
            Duration::fromSecondsAndNanoseconds(4, 0),
            75,
        ];

        yield 'percentage with nanoseconds' => [
            self::createSpan(0, 0, 2, 500_000_000),
            Duration::fromSecondsAndNanoseconds(5, 0),
            50,
        ];

        yield 'exceeding total is capped at 100 percent' => [
            self::createSpan(0, 0, 200, 0),
            Duration::fromSecondsAndNanoseconds(100, 0),
            100,
        ];
    }

    private static function createSnapshot(
        int $seconds,
        int $nanoseconds,
        int $memoryBytes,
    ): Snapshot {
        return SnapshotBuilder::withTestData()
            ->withTime(HRTime::fromSecondsAndNanoseconds($seconds, $nanoseconds))
            ->withMemoryUsage(MemoryUsage::fromBytes($memoryBytes))
            ->withPeakMemoryUsage(MemoryUsage::fromBytes($memoryBytes))
            ->build();
    }

    private static function createSpan(
        int $startSeconds,
        int $startNanoseconds,
        int $endSeconds,
        int $endNanoseconds,
    ): Span {
        return SpanBuilder::withRootTestData()
            ->withStart(self::createSnapshot($startSeconds, $startNanoseconds, 1000))
            ->withEnd(self::createSnapshot($endSeconds, $endNanoseconds, 1000))
            ->build();
    }

    private static function createGarbageCollectorStatus(): GarbageCollectorStatus
    {
        return new GarbageCollectorStatus(
            runs: 0,
            collected: 0,
            threshold: 10000,
            roots: 0,
            applicationTime: null,
            collectorTime: null,
            destructorTime: null,
            freeTime: null,
            running: null,
            protected: null,
            full: null,
            bufferSize: null,
        );
    }
}
