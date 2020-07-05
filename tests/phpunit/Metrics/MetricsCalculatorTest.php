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
use Infection\Metrics\MetricsCalculator;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutator\ZeroIteration\For_;
use Infection\Tests\Mutator\MutatorName;
use PHPUnit\Framework\TestCase;

final class MetricsCalculatorTest extends TestCase
{
    private $id = 0;

    public function test_it_shows_zero_values_by_default(): void
    {
        $calculator = new MetricsCalculator(2);

        $this->assertSame(0, $calculator->getKilledCount());
        $this->assertSame(0, $calculator->getErrorCount());
        $this->assertSame(0, $calculator->getEscapedCount());
        $this->assertSame(0, $calculator->getTimedOutCount());
        $this->assertSame(0, $calculator->getNotTestedCount());
        $this->assertSame(0, $calculator->getTotalMutantsCount());

        $this->assertSame([], $calculator->getKilledExecutionResults());
        $this->assertSame([], $calculator->getErrorExecutionResults());
        $this->assertSame([], $calculator->getEscapedExecutionResults());
        $this->assertSame([], $calculator->getTimedOutExecutionResults());
        $this->assertSame([], $calculator->getNotCoveredExecutionResults());
        $this->assertSame([], $calculator->getAllExecutionResults());

        $this->assertSame(0.0, $calculator->getMutationScoreIndicator());
        $this->assertSame(0.0, $calculator->getCoverageRate());
        $this->assertSame(0.0, $calculator->getCoveredCodeMutationScoreIndicator());
    }

    public function test_it_collects_all_values(): void
    {
        $calculator = new MetricsCalculator(2);

        $expectedKilledResults = $this->addMutantExecutionResult(
            $calculator,
            DetectionStatus::KILLED,
            7
        );
        $expectedErrorResults = $this->addMutantExecutionResult(
            $calculator,
            DetectionStatus::ERROR,
            2
        );
        $expectedEscapedResults = $this->addMutantExecutionResult(
            $calculator,
            DetectionStatus::ESCAPED,
            2
        );
        $expectedTimedOutResults = $this->addMutantExecutionResult(
            $calculator,
            DetectionStatus::TIMED_OUT,
            2
        );
        $expectedNotCoveredResults = $this->addMutantExecutionResult(
            $calculator,
            DetectionStatus::NOT_COVERED,
            1
        );

        $this->assertSame(7, $calculator->getKilledCount());
        $this->assertSame(2, $calculator->getErrorCount());
        $this->assertSame(2, $calculator->getEscapedCount());
        $this->assertSame(2, $calculator->getTimedOutCount());
        $this->assertSame(1, $calculator->getNotTestedCount());

        $this->assertSame($expectedKilledResults, $calculator->getKilledExecutionResults());
        $this->assertSame($expectedErrorResults, $calculator->getErrorExecutionResults());
        $this->assertSame($expectedEscapedResults, $calculator->getEscapedExecutionResults());
        $this->assertSame($expectedTimedOutResults, $calculator->getTimedOutExecutionResults());
        $this->assertSame($expectedNotCoveredResults, $calculator->getNotCoveredExecutionResults());
        $this->assertSame(
            array_merge(
                $expectedKilledResults,
                $expectedErrorResults,
                $expectedEscapedResults,
                $expectedTimedOutResults,
                $expectedNotCoveredResults
            ),
            $calculator->getAllExecutionResults()
        );

        $this->assertSame(14, $calculator->getTotalMutantsCount());
        $this->assertSame(78.57, $calculator->getMutationScoreIndicator());
        $this->assertSame(92.86, $calculator->getCoverageRate());
        $this->assertSame(84.62, $calculator->getCoveredCodeMutationScoreIndicator());
    }

    public function test_its_metrics_are_properly_updated_when_adding_a_new_process(): void
    {
        $calculator = new MetricsCalculator(2);

        $this->assertSame(0, $calculator->getKilledCount());
        $this->assertSame([], $calculator->getKilledExecutionResults());

        $this->assertSame(0.0, $calculator->getMutationScoreIndicator());
        $this->assertSame(0.0, $calculator->getCoverageRate());
        $this->assertSame(0.0, $calculator->getCoveredCodeMutationScoreIndicator());

        $expectedKilledResults = $this->addMutantExecutionResult(
            $calculator,
            DetectionStatus::KILLED,
            1
        );

        $this->assertSame(1, $calculator->getKilledCount());
        $this->assertSame($expectedKilledResults, $calculator->getKilledExecutionResults());

        $this->assertSame(100.0, $calculator->getMutationScoreIndicator());
        $this->assertSame(100.0, $calculator->getCoverageRate());
        $this->assertSame(100.0, $calculator->getCoveredCodeMutationScoreIndicator());
    }

    /**
     * @return MutantExecutionResult[]
     */
    private function addMutantExecutionResult(
        MetricsCalculator $calculator,
        string $detectionStatus,
        int $count
    ): array {
        $executionResults = [];

        for ($i = 0; $i < $count; ++$i) {
            $executionResults[] = $this->createMutantExecutionResult($detectionStatus);
        }

        $calculator->collect(...$executionResults);

        return $executionResults;
    }

    private function createMutantExecutionResult(string $detectionStatus): MutantExecutionResult
    {
        $id = $this->id;
        ++$this->id;

        return new MutantExecutionResult(
            'bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"',
            'process output',
            $detectionStatus,
            str_replace(
                "\n",
                PHP_EOL,
                <<<DIFF
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'mutated';

DIFF
            ),
            MutatorName::getName(For_::class),
            'foo/bar',
            $id,
            '<?php $a = 1;',
            '<?php $a = 1;'
        );
    }
}
