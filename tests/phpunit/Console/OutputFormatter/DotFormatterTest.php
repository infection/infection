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

namespace Infection\Tests\Console\OutputFormatter;

use function implode;
use Infection\Console\OutputFormatter\DotFormatter;
use Infection\Framework\Enum\EnumBucket;
use Infection\Framework\Str;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function strip_tags;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(DotFormatter::class)]
final class DotFormatterTest extends TestCase
{
    private const ANY_PRIME_NUMBER = 127;

    public function test_begins_by_displaying_a_legend(): void
    {
        $output = new BufferedOutput();
        $formatter = new DotFormatter($output);

        $expected = Str::toSystemLineEndings(
            implode(
                "\n",
                [
                    '',
                    implode(
                        ', ',
                        [
                            '<killed>.</killed>: killed by tests',
                            '<killed-by-static-analysis>A</killed-by-static-analysis>: killed by SA',
                            '<escaped>M</escaped>: escaped',
                            '<uncovered>U</uncovered>: uncovered',
                        ],
                    ),
                    implode(
                        ', ',
                        [
                            '<with-error>E</with-error>: fatal error',
                            '<with-syntax-error>X</with-syntax-error>: syntax error',
                            '<timeout>T</timeout>: timed out',
                            '<skipped>S</skipped>: skipped',
                            '<ignored>I</ignored>: ignored',
                        ],
                    ),
                    '',
                    '',
                ],
            ),
        );

        $formatter->start(10);

        $actual = $output->fetch();

        $this->assertSame($expected, $actual);
    }

    #[DataProvider('detectionStatusProvider')]
    public function test_logs_mutations_detection_status(
        DetectionStatus $detectionStatus,
        string $expected,
    ): void {
        $output = new BufferedOutput();
        $formatter = new DotFormatter($output);

        // Clear the initial output: it is already tested and it would bloat the
        // test to include it.
        $formatter->start(10);
        $output->fetch();

        $formatter->advance(
            $this->createMutantExecutionResultOfType($detectionStatus),
            10,
        );

        $actual = $output->fetch();

        $this->assertSame($expected, $actual);
    }

    public static function detectionStatusProvider(): iterable
    {
        $bucket = EnumBucket::create(DetectionStatus::class);

        yield 'killed by tests' => [
            $bucket->take(DetectionStatus::KILLED_BY_TESTS),
            '<killed>.</killed>',
        ];

        yield 'killed by SA' => [
            $bucket->take(DetectionStatus::KILLED_BY_STATIC_ANALYSIS),
            '<killed-by-static-analysis>A</killed-by-static-analysis>',
        ];

        yield 'escaped' => [
            $bucket->take(DetectionStatus::ESCAPED),
            '<escaped>M</escaped>',
        ];

        yield 'error' => [
            $bucket->take(DetectionStatus::ERROR),
            '<with-error>E</with-error>',
        ];

        yield 'syntax error' => [
            $bucket->take(DetectionStatus::SYNTAX_ERROR),
            '<with-syntax-error>X</with-syntax-error>',
        ];

        yield 'timeout' => [
            $bucket->take(DetectionStatus::TIMED_OUT),
            '<timeout>T</timeout>',
        ];

        yield 'uncovered' => [
            $bucket->take(DetectionStatus::NOT_COVERED),
            '<uncovered>U</uncovered>',
        ];

        yield 'skipped' => [
            $bucket->take(DetectionStatus::SKIPPED),
            '<skipped>S</skipped>',
        ];

        yield 'ignored' => [
            $bucket->take(DetectionStatus::IGNORED),
            '<ignored>I</ignored>',
        ];

        $bucket->assertIsEmpty();
    }

    public function test_it_prints_total_number_of_mutations(): void
    {
        $totalMutations = self::ANY_PRIME_NUMBER;

        $output = new BufferedOutput();
        $formatter = new DotFormatter($output);
        $formatter->start($totalMutations);

        for ($i = 0; $i < $totalMutations; ++$i) {
            $formatter->advance(
                $this->createMutantExecutionResultOfType(DetectionStatus::KILLED_BY_TESTS),
                $totalMutations,
            );
        }

        $expected = Str::toSystemLineEndings(
            <<<'TXT'

                .: killed by tests, A: killed by SA, M: escaped, U: uncovered
                E: fatal error, X: syntax error, T: timed out, S: skipped, I: ignored

                ..................................................   ( 50 / 127)
                ..................................................   (100 / 127)
                ...........................                          (127 / 127)
                TXT,
        );

        $actual = strip_tags($output->fetch());

        $this->assertSame($expected, $actual);
    }

    public function test_it_prints_current_number_of_pending_mutations(): void
    {
        $totalMutations = self::ANY_PRIME_NUMBER;

        $output = new BufferedOutput();
        $formatter = new DotFormatter($output);
        $formatter->start($totalMutations);

        for ($i = 0; $i < $totalMutations; ++$i) {
            $formatter->advance(
                $this->createMutantExecutionResultOfType(DetectionStatus::KILLED_BY_TESTS),
                0,
            );
        }

        $expected = Str::toSystemLineEndings(
            <<<'TXT'

                .: killed by tests, A: killed by SA, M: escaped, U: uncovered
                E: fatal error, X: syntax error, T: timed out, S: skipped, I: ignored

                ..................................................   (   50)
                ..................................................   (  100)
                ...........................
                TXT,
        );

        $actual = strip_tags($output->fetch());

        $this->assertSame($expected, $actual);
    }

    private function createMutantExecutionResultOfType(DetectionStatus $detectionStatus): MutantExecutionResult
    {
        return MutantExecutionResultBuilder::withMinimalTestData()
            ->withDetectionStatus($detectionStatus)
            ->build();
    }
}
