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

use function array_merge;
use Infection\Metrics\ResultsCollector;
use Infection\Mutant\DetectionStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResultsCollector::class)]
final class ResultsCollectorTest extends TestCase
{
    use CreateMutantExecutionResult;

    public function test_it_shows_zero_values_by_default(): void
    {
        $collector = new ResultsCollector();

        $this->assertSame([], $collector->getKilledExecutionResults());
        $this->assertSame([], $collector->getErrorExecutionResults());
        $this->assertSame([], $collector->getEscapedExecutionResults());
        $this->assertSame([], $collector->getTimedOutExecutionResults());
        $this->assertSame([], $collector->getNotCoveredExecutionResults());
        $this->assertSame([], $collector->getAllExecutionResults());
    }

    public function test_it_collects_all_values(): void
    {
        $collector = new ResultsCollector();

        $expectedKilledResults = $this->addMutantExecutionResult(
            $collector,
            DetectionStatus::KILLED_BY_TESTS,
            7,
        );
        $expectedErrorResults = $this->addMutantExecutionResult(
            $collector,
            DetectionStatus::ERROR,
            2,
        );
        $expectedEscapedResults = $this->addMutantExecutionResult(
            $collector,
            DetectionStatus::ESCAPED,
            2,
        );
        $expectedTimedOutResults = $this->addMutantExecutionResult(
            $collector,
            DetectionStatus::TIMED_OUT,
            2,
        );
        $expectedNotCoveredResults = $this->addMutantExecutionResult(
            $collector,
            DetectionStatus::NOT_COVERED,
            1,
        );

        $this->assertSame($expectedKilledResults, $collector->getKilledExecutionResults());
        $this->assertSame($expectedErrorResults, $collector->getErrorExecutionResults());
        $this->assertSame($expectedEscapedResults, $collector->getEscapedExecutionResults());
        $this->assertSame($expectedTimedOutResults, $collector->getTimedOutExecutionResults());
        $this->assertSame($expectedNotCoveredResults, $collector->getNotCoveredExecutionResults());
        $this->assertSame(
            array_merge(
                $expectedKilledResults,
                $expectedErrorResults,
                $expectedEscapedResults,
                $expectedTimedOutResults,
                $expectedNotCoveredResults,
            ),
            $collector->getAllExecutionResults(),
        );
    }

    public function test_its_metrics_are_properly_updated_when_adding_a_new_process(): void
    {
        $collector = new ResultsCollector();

        $this->assertSame([], $collector->getKilledExecutionResults());

        $expectedKilledResults = $this->addMutantExecutionResult(
            $collector,
            DetectionStatus::KILLED_BY_TESTS,
            1,
        );

        $this->assertSame($expectedKilledResults, $collector->getKilledExecutionResults());
    }
}
