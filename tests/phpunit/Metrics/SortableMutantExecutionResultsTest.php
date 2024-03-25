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

use Infection\Metrics\SortableMutantExecutionResults;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutator\Loop\For_;
use Infection\Tests\Mutator\MutatorName;
use function Later\now;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SortableMutantExecutionResults::class)]
final class SortableMutantExecutionResultsTest extends TestCase
{
    /**
     * @param MutantExecutionResult[] $executionResults
     * @param MutantExecutionResult[] $expectedResults
     */
    #[DataProvider('resultsProvider')]
    public function test_it_can_sort_results(array $executionResults, array $expectedResults): void
    {
        $sortableResults = new SortableMutantExecutionResults();

        foreach ($executionResults as $executionResult) {
            $sortableResults->add($executionResult);
        }

        $this->assertSame($expectedResults, $sortableResults->getSortedExecutionResults());
    }

    public function test_it_keeps_results_sorted_as_they_are_added(): void
    {
        $sortableResults = new SortableMutantExecutionResults();

        $result0 = self::createExecutionResult(
            0,
            '/path/to/Foo.php',
            10,
        );
        $result1 = self::createExecutionResult(
            1,
            '/path/to/Bar.php',
            10,
        );
        $result2 = self::createExecutionResult(
            2,
            '/path/to/Bar.php',
            13,
        );

        $sortableResults->add($result0);
        $sortableResults->add($result1);

        $this->assertSame(
            [$result1, $result0],
            $sortableResults->getSortedExecutionResults(),
        );

        $sortableResults->add($result2);

        $this->assertSame(
            [$result1, $result2, $result0],
            $sortableResults->getSortedExecutionResults(),
        );
    }

    public static function resultsProvider(): iterable
    {
        yield 'empty' => [[], []];

        yield 'single result' => (static function (): array {
            $results = [self::createExecutionResult(
                0,
                '/path/to/Foo.php',
                10,
            )];

            return [$results, $results];
        })();

        yield 'two identical results' => (static function (): array {
            $result0 = self::createExecutionResult(
                0,
                '/path/to/Foo.php',
                10,
            );

            return [[$result0, $result0], [$result0, $result0]];
        })();

        yield 'two different unordered results - sort by file path' => (static function (): array {
            $result0 = self::createExecutionResult(
                0,
                '/path/to/Foo.php',
                10,
            );
            $result1 = self::createExecutionResult(
                1,
                '/path/to/Bar.php',
                10,
            );

            return [[$result0, $result1], [$result1, $result0]];
        })();

        yield 'two different ordered results - sort by file path' => (static function (): array {
            $result0 = self::createExecutionResult(
                0,
                '/path/to/Foo.php',
                10,
            );
            $result1 = self::createExecutionResult(
                1,
                '/path/to/Bar.php',
                10,
            );

            return [[$result1, $result0], [$result1, $result0]];
        })();

        yield 'two different unordered results with same file path - sort by original starting line' => (static function (): array {
            $result0 = self::createExecutionResult(
                0,
                '/path/to/Foo.php',
                15,
            );
            $result1 = self::createExecutionResult(
                1,
                '/path/to/Foo.php',
                10,
            );

            return [[$result0, $result1], [$result1, $result0]];
        })();

        yield 'two different ordered results with same file path - sort by original starting line' => (static function (): array {
            $result0 = self::createExecutionResult(
                0,
                '/path/to/Foo.php',
                15,
            );
            $result1 = self::createExecutionResult(
                1,
                '/path/to/Foo.php',
                10,
            );

            return [[$result1, $result0], [$result1, $result0]];
        })();
    }

    private static function createExecutionResult(
        int $id,
        string $originalFilePath,
        int $originalStartingLine,
    ): MutantExecutionResult {
        return new MutantExecutionResult(
            'bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"',
            'Passed!',
            DetectionStatus::ESCAPED,
            now('#' . $id),
            '#' . $id,
            MutatorName::getName(For_::class),
            $originalFilePath,
            $originalStartingLine,
            $originalStartingLine + 10,
            1,
            5,
            now('<?php $a = 1;'),
            now('<?php $a = 1;'),
            [],
        );
    }
}
