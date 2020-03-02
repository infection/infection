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

use Generator;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutant\SortableMutantExecutionResults;
use Infection\Mutator\ZeroIteration\For_;
use Infection\Process\MutantProcess;
use Infection\Tests\Mutator\MutatorName;
use PHPUnit\Framework\TestCase;

final class SortableMutantExecutionResultsTest extends TestCase
{
    /**
     * @dataProvider resultsProvider
     *
     * @param MutantExecutionResult[] $executionResults
     * @param MutantExecutionResult[] $expectedResults
     */
    public function test_it_can_sort_processes(array $executionResults, array $expectedResults): void
    {
        $sortableResults = new SortableMutantExecutionResults();

        foreach ($executionResults as $executionResult) {
            $sortableResults->add($executionResult);
        }

        $this->assertSame($expectedResults, $sortableResults->getSortedExecutionResults());
    }

    public function resultsProvider(): Generator
    {
        yield 'empty' => [[], []];

        yield 'single result' => (function (): array {
            $results = [$this->createExecutionResult(
                0,
                '/path/to/Foo.php',
                10
            )];

            return [$results, $results];
        })();

        yield 'two identical results' => (function (): array {
            $result0 = $this->createExecutionResult(
                0,
                '/path/to/Foo.php',
                10
            );

            return [[$result0, $result0], [$result0, $result0]];
        })();

        yield 'two different unordered results - sort by file path' => (function (): array {
            $result0 = $this->createExecutionResult(
                0,
                '/path/to/Foo.php',
                10
            );
            $result1 = $this->createExecutionResult(
                1,
                '/path/to/Bar.php',
                10
            );

            return [[$result0, $result1], [$result1, $result0]];
        })();

        yield 'two different ordered results - sort by file path' => (function (): array {
            $result0 = $this->createExecutionResult(
                0,
                '/path/to/Foo.php',
                10
            );
            $result1 = $this->createExecutionResult(
                1,
                '/path/to/Bar.php',
                10
            );

            return [[$result1, $result0], [$result1, $result0]];
        })();

        yield 'two different unordered results with same file path - sort by original starting line' => (function (): array {
            $result0 = $this->createExecutionResult(
                0,
                '/path/to/Foo.php',
                15
            );
            $result1 = $this->createExecutionResult(
                1,
                '/path/to/Foo.php',
                10
            );

            return [[$result0, $result1], [$result1, $result0]];
        })();

        yield 'two different ordered results with same file path - sort by original starting line' => (function (): array {
            $result0 = $this->createExecutionResult(
                0,
                '/path/to/Foo.php',
                15
            );
            $result1 = $this->createExecutionResult(
                1,
                '/path/to/Foo.php',
                10
            );

            return [[$result1, $result0], [$result1, $result0]];
        })();
    }

    private function createExecutionResult(
        int $id,
        string $originalFilePath,
        int $originalStartingLine
    ): MutantExecutionResult {
        return new MutantExecutionResult(
            'bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"',
            'Passed!',
            MutantProcess::CODE_ESCAPED,
            '#' . $id,
            MutatorName::getName(For_::class),
            $originalFilePath,
            $originalStartingLine
        );
    }
}
