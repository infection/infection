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

use Infection\Metrics\Collector;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutator\Loop\For_;
use Infection\Testing\MutatorName;
use function Later\now;
use const PHP_EOL;
use function str_replace;

trait CreateMutantExecutionResult
{
    private int $id = 0;

    /**
     * @return MutantExecutionResult[]
     */
    private function addMutantExecutionResult(
        Collector $collector,
        DetectionStatus $detectionStatus,
        int $count = 1,
    ): array {
        $executionResults = [];

        for ($i = 0; $i < $count; ++$i) {
            $executionResults[] = $this->createMutantExecutionResult($detectionStatus);
        }

        $collector->collect(...$executionResults);

        return $executionResults;
    }

    private function createMutantExecutionResult(DetectionStatus $detectionStatus): MutantExecutionResult
    {
        $id = $this->id;
        ++$this->id;

        return new MutantExecutionResult(
            'bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"',
            'process output',
            $detectionStatus,
            now(str_replace(
                "\n",
                PHP_EOL,
                <<<DIFF
                    --- Original
                    +++ New
                    @@ @@

                    - echo 'original';
                    + echo 'mutated';

                    DIFF,
            )),
            'a1b2c3',
            For_::class,
            MutatorName::getName(For_::class),
            'foo/bar',
            $id,
            10 + $id,
            $id,
            10 + $id,
            now('<?php $a = 1;'),
            now('<?php $a = 1;'),
            [],
            0.0,
        );
    }
}
