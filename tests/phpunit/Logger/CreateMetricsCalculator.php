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

namespace Infection\Tests\Logger;

use Infection\Metrics\MetricsCalculator;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutator\Regex\PregQuote;
use Infection\Mutator\ZeroIteration\For_;
use Infection\Tests\Mutator\MutatorName;
use function Infection\Tests\normalize_trailing_spaces;

trait CreateMetricsCalculator
{
    private function createCompleteMetricsCalculator(): MetricsCalculator
    {
        $calculator = new MetricsCalculator(2);

        $calculator->collect(
            $this->createMutantExecutionResult(
                0,
                For_::class,
                DetectionStatus::KILLED,
                'killed#0'
            ),
            $this->createMutantExecutionResult(
                1,
                PregQuote::class,
                DetectionStatus::KILLED,
                'killed#1'
            ),
            $this->createMutantExecutionResult(
                0,
                For_::class,
                DetectionStatus::ERROR,
                'error#0'
            ),
            $this->createMutantExecutionResult(
                1,
                PregQuote::class,
                DetectionStatus::ERROR,
                'error#1'
            ),
            $this->createMutantExecutionResult(
                0,
                For_::class,
                DetectionStatus::ESCAPED,
                'escaped#0'
            ),
            $this->createMutantExecutionResult(
                1,
                PregQuote::class,
                DetectionStatus::ESCAPED,
                'escaped#1'
            ),
            $this->createMutantExecutionResult(
                0,
                For_::class,
                DetectionStatus::TIMED_OUT,
                'timedOut#0'
            ),
            $this->createMutantExecutionResult(
                1,
                PregQuote::class,
                DetectionStatus::TIMED_OUT,
                'timedOut#1'
            ),
            $this->createMutantExecutionResult(
                0,
                For_::class,
                DetectionStatus::SKIPPED,
                'skipped#0'
            ),
            $this->createMutantExecutionResult(
                0,
                PregQuote::class,
                DetectionStatus::SKIPPED,
                'skipped#1'
            ),
            $this->createMutantExecutionResult(
                0,
                For_::class,
                DetectionStatus::NOT_COVERED,
                'notCovered#0'
            ),
            $this->createMutantExecutionResult(
                1,
                PregQuote::class,
                DetectionStatus::NOT_COVERED,
                'notCovered#1'
            )
        );

        return $calculator;
    }

    private function createMutantExecutionResult(
        int $i,
        string $mutatorClassName,
        string $detectionStatus,
        string $echoMutatedMessage
    ): MutantExecutionResult {
        return new MutantExecutionResult(
            'bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"',
            'process output',
            $detectionStatus,
            normalize_trailing_spaces(
                <<<DIFF
--- Original
+++ New
@@ @@

- echo 'original';
+ echo '$echoMutatedMessage';

DIFF
            ),
            MutatorName::getName($mutatorClassName),
            'foo/bar',
            10 - $i,
            '<?php $a = 1;',
            '<?php $a = 2;'
        );
    }
}
