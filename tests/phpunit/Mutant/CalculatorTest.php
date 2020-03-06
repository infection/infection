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

namespace Infection\Tests\Mutant;

use function array_sum;
use Generator;
use Infection\Mutant\Calculator;
use Infection\Mutant\MetricsCalculator;
use Infection\Tests\Logger\CreateMetricsCalculator;
use PHPUnit\Framework\TestCase;

final class CalculatorTest extends TestCase
{
    use CreateMetricsCalculator;

    /**
     * @dataProvider metricsProvider
     */
    public function test_it_can_calculate_the_scores(
        int $killedCount,
        int $errorCount,
        int $escapedCount,
        int $timedOutCount,
        int $notTestedCount,
        float $expectedMsi,
        float $expectedCoverageRate,
        float $expectedCoveredMsi
    ): void {
        $calculator = new Calculator(
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
            ])
        );

        $this->assertSame($expectedMsi, $calculator->getMutationScoreIndicator());
        $this->assertSame($expectedCoverageRate, $calculator->getCoverageRate());
        $this->assertSame($expectedCoveredMsi, $calculator->getCoveredCodeMutationScoreIndicator());

        // The calls are idempotent
        $this->assertSame($expectedMsi, $calculator->getMutationScoreIndicator());
        $this->assertSame($expectedCoverageRate, $calculator->getCoverageRate());
        $this->assertSame($expectedCoveredMsi, $calculator->getCoveredCodeMutationScoreIndicator());
    }

    /**
     * @dataProvider metricsWithTimeoutProvider
     */
    public function test_it_can_calculate_the_scores_while_counting_timeouts_as_escapes(
        int $killedCount,
        int $errorCount,
        int $escapedCount,
        int $timedOutCount,
        int $notTestedCount,
        float $expectedMsi,
        float $expectedCoverageRate,
        float $expectedCoveredMsi
    ): void {
        $calculator = new Calculator(
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
            true
        );

        $this->assertSame($expectedMsi, $calculator->getMutationScoreIndicator());
        $this->assertSame($expectedCoverageRate, $calculator->getCoverageRate());
        $this->assertSame($expectedCoveredMsi, $calculator->getCoveredCodeMutationScoreIndicator());

        // The calls are idempotent
        $this->assertSame($expectedMsi, $calculator->getMutationScoreIndicator());
        $this->assertSame($expectedCoverageRate, $calculator->getCoverageRate());
        $this->assertSame($expectedCoveredMsi, $calculator->getCoveredCodeMutationScoreIndicator());
    }

    /**
     * @dataProvider metricsCalculatorProvider
     */
    public function test_it_can_be_created_from_a_metrics_calculator(
        MetricsCalculator $metricsCalculator,
        float $expectedMsi,
        float $expectedCoverageRate,
        float $expectedCoveredMsi
    ): void {
        $calculator = Calculator::fromMetrics($metricsCalculator);

        $this->assertSame($expectedMsi, $calculator->getMutationScoreIndicator());
        $this->assertSame($expectedCoverageRate, $calculator->getCoverageRate());
        $this->assertSame($expectedCoveredMsi, $calculator->getCoveredCodeMutationScoreIndicator());
    }

    public function metricsProvider(): Generator
    {
        yield 'empty' => [
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
            7,
            2,
            2,
            2,
            1,
            78.57142857142857,
            92.85714285714286,
            84.61538461538461,
        ];

        yield 'nominal no non-tested' => [
            7,
            2,
            2,
            2,
            0,
            84.61538461538461,
            100,
            84.61538461538461,
        ];
    }

    public function metricsWithTimeoutProvider(): Generator
    {
        yield 'empty' => [
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
            7,
            2,
            2,
            2,
            1,
            64.285714285714292,
            92.85714285714286,
            69.230769230769226,
        ];

        yield 'nominal no non-tested' => [
            7,
            2,
            2,
            2,
            0,
            69.230769230769226,
            100,
            69.230769230769226,
        ];
    }

    public function metricsCalculatorProvider(): Generator
    {
        yield 'empty' => [
            new MetricsCalculator(),
            0.,
            0.,
            0.,
        ];

        yield 'nominal' => [
            $this->createCompleteMetricsCalculator(),
            60.,
            80.0,
            75.0,
        ];
    }
}
