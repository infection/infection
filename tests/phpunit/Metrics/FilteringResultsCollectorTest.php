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

use function array_flip;
use Infection\Metrics\Collector;
use Infection\Metrics\FilteringResultsCollector;
use Infection\Mutant\DetectionStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilteringResultsCollector::class)]
final class FilteringResultsCollectorTest extends TestCase
{
    use CreateMutantExecutionResult;

    public function test_it_collects_nothing_by_default(): void
    {
        $targetCollector = $this->createMock(Collector::class);
        $targetCollector
            ->expects($this->never())
            ->method('collect')
        ;

        $collector = new FilteringResultsCollector($targetCollector, []);

        $this->addMutantExecutionResult(
            $collector,
            DetectionStatus::ESCAPED,
            2,
        );
    }

    public function test_it_collects_everything_when_told_to(): void
    {
        $targetCollector = $this->createMock(Collector::class);
        $targetCollector
            ->expects($this->exactly(5))
            ->method('collect')
        ;

        $targetDetectionStatuses = array_flip(DetectionStatus::ALL);

        $collector = new FilteringResultsCollector($targetCollector, $targetDetectionStatuses);

        $this->addSeveralMutantExecutionResults($collector);
    }

    public function test_it_does_not_collect_everything_when_told_to(): void
    {
        $targetCollector = $this->createMock(Collector::class);
        $targetCollector
            ->expects($this->exactly(4))
            ->method('collect')
        ;

        $targetDetectionStatuses = array_flip(DetectionStatus::ALL);
        unset($targetDetectionStatuses[DetectionStatus::KILLED_BY_TESTS]);

        $collector = new FilteringResultsCollector($targetCollector, $targetDetectionStatuses);

        $this->addSeveralMutantExecutionResults($collector);
    }

    private function addSeveralMutantExecutionResults(Collector $collector): void
    {
        $this->addMutantExecutionResult(
            $collector,
            DetectionStatus::KILLED_BY_TESTS,
            7,
        );

        $this->addMutantExecutionResult(
            $collector,
            DetectionStatus::ERROR,
            2,
        );

        $this->addMutantExecutionResult(
            $collector,
            DetectionStatus::ESCAPED,
            2,
        );

        $this->addMutantExecutionResult(
            $collector,
            DetectionStatus::TIMED_OUT,
            2,
        );

        $this->addMutantExecutionResult(
            $collector,
            DetectionStatus::NOT_COVERED,
            1,
        );
    }
}
