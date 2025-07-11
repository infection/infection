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

namespace Infection\Tests\Metrics;

use function array_sum;
use Infection\Metrics\Calculator;
use Infection\Metrics\MetricsCalculator;
use Infection\Tests\Logger\CreateMetricsCalculator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Calculator::class)]
final class CalculatorTest extends TestCase
{
    use CreateMetricsCalculator;

    #[DataProvider('metricsProvider')]
    public function test_it_can_calculate_the_scores(
        int $roundingPrecision,
        int $killedCount,
        int $errorCount,
        int $escapedCount,
        int $timedOutCount,
        int $notTestedCount,
        float $expectedMsi,
        float $expectedCoverageRate,
        float $expectedCoveredMsi,
    ): void {
        $calculator = new Calculator(
            $roundingPrecision,
            $killedCount,
            $errorCount,
            $timedOutCount,
            $notTestedCount,
            array_sum([
                $killedCount,
                $errorCount,
                $escapedCount,
                $timedOutCount,
                $notTestedCount,
            ]),
        );

        $this->assertSame($expectedMsi, $calculator->getMutationScoreIndicator());
        $this->assertSame($expectedCoverageRate, $calculator->getCoverageRate());
        $this->assertSame($expectedCoveredMsi, $calculator->getCoveredCodeMutationScoreIndicator());

        // The calls are idempotent
        $this->assertSame($expectedMsi, $calculator->getMutationScoreIndicator());
        $this->assertSame($expectedCoverageRate, $calculator->getCoverageRate());
        $this->assertSame($expectedCoveredMsi, $calculator->getCoveredCodeMutationScoreIndicator());
    }

    #[DataProvider('metricsCalculatorProvider')]
    public function test_it_can_be_created_from_a_metrics_calculator(
        MetricsCalculator $metricsCalculator,
        float $expectedMsi,
        float $expectedCoverageRate,
        float $expectedCoveredMsi,
    ): void {
        $calculator = Calculator::fromMetrics($metricsCalculator);

        $this->assertSame($expectedMsi, $calculator->getMutationScoreIndicator());
        $this->assertSame($expectedCoverageRate, $calculator->getCoverageRate());
        $this->assertSame($expectedCoveredMsi, $calculator->getCoveredCodeMutationScoreIndicator());
    }

    public static function metricsProvider(): iterable
    {
        yield 'empty' => [
            2,
            0,
            0,
            0,
            0,
            0,
            0.,
            0.,
            0.,
        ];

        yield 'int scores' => [
            2,
            1,
            0,
            9,
            0,
            0,
            10.,
            100.0,
            10.0,
        ];

        yield 'nominal' => [
            2,
            7,
            2,
            2,
            2,
            1,
            78.57,
            92.86,
            84.62,
        ];

        yield 'nominal with higher precision' => [
            4,
            7,
            2,
            2,
            2,
            1,
            78.5714,
            92.8571,
            84.6154,
        ];

        yield 'nominal no non-tested' => [
            2,
            7,
            2,
            2,
            2,
            0,
            84.62,
            100,
            84.62,
        ];
    }

    public static function metricsCalculatorProvider(): iterable
    {
        yield 'empty' => [
            new MetricsCalculator(2),
            0.,
            0.,
            0.,
        ];

        yield 'nominal' => [
            self::createCompleteMetricsCalculator(),
            69.23, // 15 total mutations; 2 skipped; 9 of 13 are killed => 69.23%
            84.62, // 15 total mutations; 2 skipped & 2 not covered; => 11 of 13 => 83.33%
            81.82, // 15 total mutations; 2 skipped & 2 not covered; 9 of 11 are killed => 80%
        ];
    }
}
