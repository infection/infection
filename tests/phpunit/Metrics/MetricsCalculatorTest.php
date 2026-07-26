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

use Infection\Metrics\MetricsCalculator;
use Infection\Mutant\DetectionStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

#[CoversClass(MetricsCalculator::class)]
final class MetricsCalculatorTest extends TestCase
{
    use CreateMutantExecutionResult;

    public function test_it_shows_zero_values_by_default(): void
    {
        $calculator = new MetricsCalculator(2);

        $this->assertSame(0, $calculator->getKilledByTestsCount());
        $this->assertSame(0, $calculator->getErrorCount());
        $this->assertSame(0, $calculator->getEscapedCount());
        $this->assertSame(0, $calculator->getTimedOutCount());
        $this->assertSame(0, $calculator->getNotTestedCount());
        $this->assertSame(0, $calculator->getTotalMutantsCount());

        $this->assertSame(0.0, $calculator->getMutationScoreIndicator());
        $this->assertSame(0.0, $calculator->getCoverageRate());
        $this->assertSame(0.0, $calculator->getCoveredCodeMutationScoreIndicator());
    }

    public function test_it_collects_all_values(): void
    {
        $calculator = new MetricsCalculator(2);

        $this->addMutantExecutionResult(
            $calculator,
            DetectionStatus::KILLED_BY_TESTS,
            7,
        );
        $this->addMutantExecutionResult(
            $calculator,
            DetectionStatus::ERROR,
            2,
        );
        $this->addMutantExecutionResult(
            $calculator,
            DetectionStatus::ESCAPED,
            2,
        );
        $this->addMutantExecutionResult(
            $calculator,
            DetectionStatus::TIMED_OUT,
            2,
        );
        $this->addMutantExecutionResult(
            $calculator,
            DetectionStatus::NOT_COVERED,
            1,
        );

        $this->assertSame(7, $calculator->getKilledByTestsCount());
        $this->assertSame(2, $calculator->getErrorCount());
        $this->assertSame(2, $calculator->getEscapedCount());
        $this->assertSame(2, $calculator->getTimedOutCount());
        $this->assertSame(1, $calculator->getNotTestedCount());

        $this->assertSame(14, $calculator->getTotalMutantsCount());
        $this->assertSame(78.57, $calculator->getMutationScoreIndicator());
        $this->assertSame(92.86, $calculator->getCoverageRate());
        $this->assertSame(84.62, $calculator->getCoveredCodeMutationScoreIndicator());
    }

    public function test_its_metrics_are_properly_updated_when_adding_a_new_process(): void
    {
        $calculator = new MetricsCalculator(2);

        $this->assertSame(0, $calculator->getKilledByTestsCount());

        $this->assertSame(0.0, $calculator->getMutationScoreIndicator());
        $this->assertSame(0.0, $calculator->getCoverageRate());
        $this->assertSame(0.0, $calculator->getCoveredCodeMutationScoreIndicator());

        $this->addMutantExecutionResult(
            $calculator,
            DetectionStatus::KILLED_BY_TESTS,
            1,
        );

        $this->assertSame(1, $calculator->getKilledByTestsCount());

        $this->assertSame(100.0, $calculator->getMutationScoreIndicator());
        $this->assertSame(100.0, $calculator->getCoverageRate());
        $this->assertSame(100.0, $calculator->getCoveredCodeMutationScoreIndicator());
    }

    public function test_calculator_is_memoized(): void
    {
        $metricsCalculator = new MetricsCalculator(2);

        $this->addMutantExecutionResult(
            $metricsCalculator,
            DetectionStatus::KILLED_BY_TESTS,
            1,
        );

        // First call creates and memoizes Calculator
        $metricsCalculator->getMutationScoreIndicator();

        $calculatorProperty = new ReflectionProperty($metricsCalculator, 'calculator');
        $firstCalculator = $calculatorProperty->getValue($metricsCalculator);

        // Second call should reuse memoized Calculator
        $metricsCalculator->getCoveredCodeMutationScoreIndicator();

        $secondCalculator = $calculatorProperty->getValue($metricsCalculator);

        $this->assertSame($firstCalculator, $secondCalculator, 'Calculator should be memoized between calls');
    }
}
