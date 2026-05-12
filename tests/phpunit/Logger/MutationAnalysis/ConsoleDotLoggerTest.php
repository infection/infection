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

namespace Infection\Tests\Logger\MutationAnalysis;

use function implode;
use Infection\Framework\Enum\EnumBucket;
use Infection\Framework\Iterable\IterableCounter;
use Infection\Framework\Str;
use Infection\Logger\MutationAnalysis\ConsoleDotLogger;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function strip_tags;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Terminal;

#[CoversClass(ConsoleDotLogger::class)]
final class ConsoleDotLoggerTest extends TestCase
{
    private const int ANY_PRIME_NUMBER = 127;

    public function test_begins_by_displaying_a_legend(): void
    {
        $output = new BufferedOutput();
        $logger = new ConsoleDotLogger($output);

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

        $logger->startAnalysis(10);

        $actual = $output->fetch();

        $this->assertSame($expected, $actual);
    }

    #[DataProvider('detectionStatusProvider')]
    public function test_logs_mutations_detection_status(
        DetectionStatus $detectionStatus,
        string $expected,
    ): void {
        $output = new BufferedOutput();
        $logger = new ConsoleDotLogger($output);

        // Clear the initial output: it is already tested and it would bloat the
        // test to include it.
        $logger->startAnalysis(10);
        $output->fetch();

        $logger->finishEvaluation(
            $this->createMutantExecutionResultOfType($detectionStatus),
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
        $logger = new ConsoleDotLogger($output);
        $logger->startAnalysis($totalMutations);

        for ($i = 0; $i < $totalMutations; ++$i) {
            $logger->finishEvaluation(
                $this->createMutantExecutionResultOfType(DetectionStatus::KILLED_BY_TESTS),
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

    public function test_it_prints_current_number_of_processed_mutations_when_the_total_number_is_not_known(): void
    {
        $totalMutations = self::ANY_PRIME_NUMBER;

        $output = new BufferedOutput();
        $logger = new ConsoleDotLogger($output);
        $logger->startAnalysis(IterableCounter::UNKNOWN_COUNT);

        for ($i = 0; $i < $totalMutations; ++$i) {
            $logger->finishEvaluation(
                $this->createMutantExecutionResultOfType(DetectionStatus::KILLED_BY_TESTS),
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

    public function test_it_honors_a_custom_dots_per_row(): void
    {
        $totalMutations = 23;

        $output = new BufferedOutput();
        $logger = new ConsoleDotLogger($output, dotsPerRowSetting: 10);
        $logger->startAnalysis($totalMutations);

        for ($i = 0; $i < $totalMutations; ++$i) {
            $logger->finishEvaluation(
                $this->createMutantExecutionResultOfType(DetectionStatus::KILLED_BY_TESTS),
            );
        }

        $expected = Str::toSystemLineEndings(
            <<<'TXT'

                .: killed by tests, A: killed by SA, M: escaped, U: uncovered
                E: fatal error, X: syntax error, T: timed out, S: skipped, I: ignored

                ..........   (10 / 23)
                ..........   (20 / 23)
                ...          (23 / 23)
                TXT,
        );

        $actual = strip_tags($output->fetch());

        $this->assertSame($expected, $actual);
    }

    public function test_it_resolves_max_to_terminal_width_minus_suffix(): void
    {
        // With a 30-column terminal and a 2-digit total, the (NN / NN) suffix
        // takes 12 characters, leaving 18 columns for dots.
        $totalMutations = 25;

        $output = new BufferedOutput();
        $logger = new ConsoleDotLogger(
            $output,
            dotsPerRowSetting: 'max',
            terminal: $this->terminalWithWidth(30),
        );
        $logger->startAnalysis($totalMutations);

        for ($i = 0; $i < $totalMutations; ++$i) {
            $logger->finishEvaluation(
                $this->createMutantExecutionResultOfType(DetectionStatus::KILLED_BY_TESTS),
            );
        }

        $expected = Str::toSystemLineEndings(
            <<<'TXT'

                .: killed by tests, A: killed by SA, M: escaped, U: uncovered
                E: fatal error, X: syntax error, T: timed out, S: skipped, I: ignored

                ..................   (18 / 25)
                .......              (25 / 25)
                TXT,
        );

        $actual = strip_tags($output->fetch());

        $this->assertSame($expected, $actual);
    }

    public function test_it_falls_back_to_one_dot_per_row_when_the_terminal_is_narrower_than_the_suffix(): void
    {
        // Width 5 is below the 10-character (n / total) suffix length for a
        // 1-digit total, so the resolved row width is floored at 1.
        $totalMutations = 3;

        $output = new BufferedOutput();
        $logger = new ConsoleDotLogger(
            $output,
            dotsPerRowSetting: 'max',
            terminal: $this->terminalWithWidth(5),
        );
        $logger->startAnalysis($totalMutations);

        for ($i = 0; $i < $totalMutations; ++$i) {
            $logger->finishEvaluation(
                $this->createMutantExecutionResultOfType(DetectionStatus::KILLED_BY_TESTS),
            );
        }

        $expected = Str::toSystemLineEndings(
            <<<'TXT'

                .: killed by tests, A: killed by SA, M: escaped, U: uncovered
                E: fatal error, X: syntax error, T: timed out, S: skipped, I: ignored

                .   (1 / 3)
                .   (2 / 3)
                .   (3 / 3)
                TXT,
        );

        $actual = strip_tags($output->fetch());

        $this->assertSame($expected, $actual);
    }

    public function test_it_caps_max_at_the_total_mutation_count(): void
    {
        $totalMutations = 7;

        $output = new BufferedOutput();
        $logger = new ConsoleDotLogger(
            $output,
            dotsPerRowSetting: 'max',
            terminal: $this->terminalWithWidth(500),
        );
        $logger->startAnalysis($totalMutations);

        for ($i = 0; $i < $totalMutations; ++$i) {
            $logger->finishEvaluation(
                $this->createMutantExecutionResultOfType(DetectionStatus::KILLED_BY_TESTS),
            );
        }

        $expected = Str::toSystemLineEndings(
            <<<'TXT'

                .: killed by tests, A: killed by SA, M: escaped, U: uncovered
                E: fatal error, X: syntax error, T: timed out, S: skipped, I: ignored

                .......   (7 / 7)
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

    private function terminalWithWidth(int $width): Terminal
    {
        $terminal = $this->createMock(Terminal::class);
        $terminal->method('getWidth')->willReturn($width);

        return $terminal;
    }
}
