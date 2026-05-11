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

    private const int DEFAULT_DOTS_PER_ROW = 50;

    private const int ANY_TERMINAL_WIDTH = 80;

    public function test_begins_by_displaying_a_legend(): void
    {
        $output = new BufferedOutput();
        $logger = new ConsoleDotLogger($output, self::DEFAULT_DOTS_PER_ROW, $this->createTerminal(self::ANY_TERMINAL_WIDTH));

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

        $logger->startEvaluation(10);

        $actual = $output->fetch();

        $this->assertSame($expected, $actual);
    }

    #[DataProvider('detectionStatusProvider')]
    public function test_logs_mutations_detection_status(
        DetectionStatus $detectionStatus,
        string $expected,
    ): void {
        $output = new BufferedOutput();
        $logger = new ConsoleDotLogger($output, self::DEFAULT_DOTS_PER_ROW, $this->createTerminal(self::ANY_TERMINAL_WIDTH));

        // Clear the initial output: it is already tested and it would bloat the
        // test to include it.
        $logger->startEvaluation(10);
        $output->fetch();

        $logger->finishEvaluationForMutation(
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

    /**
     * @param positive-int $totalMutations
     * @param positive-int|'max' $dotsPerRowSetting
     * @param positive-int $terminalWidth
     */
    #[DataProvider('totalMutationsProvider')]
    public function test_it_prints_total_number_of_mutations(
        int $totalMutations,
        string|int $dotsPerRowSetting,
        int $terminalWidth,
        string $expected,
    ): void {
        $output = new BufferedOutput();
        $logger = new ConsoleDotLogger(
            $output,
            $dotsPerRowSetting,
            $this->createTerminal($terminalWidth),
        );
        $logger->startEvaluation($totalMutations);

        for ($i = 0; $i < $totalMutations; ++$i) {
            $logger->finishEvaluationForMutation(
                $this->createMutantExecutionResultOfType(DetectionStatus::KILLED_BY_TESTS),
            );
        }

        $actual = strip_tags($output->fetch());

        $this->assertSame(Str::toSystemLineEndings($expected), $actual);
    }

    public static function totalMutationsProvider(): iterable
    {
        yield 'default 50 dots per row' => [
            self::ANY_PRIME_NUMBER,
            self::DEFAULT_DOTS_PER_ROW,
            self::ANY_TERMINAL_WIDTH,
            <<<'TXT'

                .: killed by tests, A: killed by SA, M: escaped, U: uncovered
                E: fatal error, X: syntax error, T: timed out, S: skipped, I: ignored

                ..................................................   ( 50 / 127)
                ..................................................   (100 / 127)
                ...........................                          (127 / 127)
                TXT,
        ];

        yield 'custom 10 dots per row' => [
            23,
            10,
            self::ANY_TERMINAL_WIDTH,
            <<<'TXT'

                .: killed by tests, A: killed by SA, M: escaped, U: uncovered
                E: fatal error, X: syntax error, T: timed out, S: skipped, I: ignored

                ..........   (10 / 23)
                ..........   (20 / 23)
                ...          (23 / 23)
                TXT,
        ];

        // With a 30-column terminal and a 2-digit total, the (NN / NN) suffix
        // takes 12 characters, leaving 18 columns for dots.
        yield 'max fits the terminal width minus the suffix' => [
            25,
            'max',
            30,
            <<<'TXT'

                .: killed by tests, A: killed by SA, M: escaped, U: uncovered
                E: fatal error, X: syntax error, T: timed out, S: skipped, I: ignored

                ..................   (18 / 25)
                .......              (25 / 25)
                TXT,
        ];

        // Width 5 is below the 10-character (n / total) suffix length for a
        // 1-digit total, so the resolved row width is floored at 1.
        yield 'max falls back to a single dot per row on very narrow terminals' => [
            3,
            'max',
            5,
            <<<'TXT'

                .: killed by tests, A: killed by SA, M: escaped, U: uncovered
                E: fatal error, X: syntax error, T: timed out, S: skipped, I: ignored

                .   (1 / 3)
                .   (2 / 3)
                .   (3 / 3)
                TXT,
        ];

        yield 'max is capped at the total mutation count' => [
            7,
            'max',
            500,
            <<<'TXT'

                .: killed by tests, A: killed by SA, M: escaped, U: uncovered
                E: fatal error, X: syntax error, T: timed out, S: skipped, I: ignored

                .......   (7 / 7)
                TXT,
        ];
    }

    /**
     * @param positive-int $evaluationsCount
     * @param positive-int|'max' $dotsPerRowSetting
     * @param positive-int $terminalWidth
     */
    #[DataProvider('unknownTotalProvider')]
    public function test_it_prints_current_number_of_processed_mutations_when_the_total_number_is_not_known(
        int $evaluationsCount,
        string|int $dotsPerRowSetting,
        int $terminalWidth,
        string $expected,
    ): void {
        $output = new BufferedOutput();
        $logger = new ConsoleDotLogger($output, $dotsPerRowSetting, $this->createTerminal($terminalWidth));
        $logger->startEvaluation(IterableCounter::UNKNOWN_COUNT);

        for ($i = 0; $i < $evaluationsCount; ++$i) {
            $logger->finishEvaluationForMutation(
                $this->createMutantExecutionResultOfType(DetectionStatus::KILLED_BY_TESTS),
            );
        }

        $actual = strip_tags($output->fetch());

        $this->assertSame(Str::toSystemLineEndings($expected), $actual);
    }

    public static function unknownTotalProvider(): iterable
    {
        yield 'default 50 dots per row' => [
            self::ANY_PRIME_NUMBER,
            self::DEFAULT_DOTS_PER_ROW,
            self::ANY_TERMINAL_WIDTH,
            <<<'TXT'

                .: killed by tests, A: killed by SA, M: escaped, U: uncovered
                E: fatal error, X: syntax error, T: timed out, S: skipped, I: ignored

                ..................................................   (   50)
                ..................................................   (  100)
                ...........................
                TXT,
        ];

        yield 'custom 10 dots per row' => [
            23,
            10,
            self::ANY_TERMINAL_WIDTH,
            <<<'TXT'

                .: killed by tests, A: killed by SA, M: escaped, U: uncovered
                E: fatal error, X: syntax error, T: timed out, S: skipped, I: ignored

                ..........   (   10)
                ..........   (   20)
                ...
                TXT,
        ];

        // With a 30-column terminal and a 10-character (NNNNN) suffix, 20
        // columns remain for dots.
        yield 'max fits the terminal width minus the suffix' => [
            25,
            'max',
            30,
            <<<'TXT'

                .: killed by tests, A: killed by SA, M: escaped, U: uncovered
                E: fatal error, X: syntax error, T: timed out, S: skipped, I: ignored

                ....................   (   20)
                .....
                TXT,
        ];

        yield 'max falls back to a single dot per row on very narrow terminals' => [
            3,
            'max',
            5,
            <<<'TXT'

                .: killed by tests, A: killed by SA, M: escaped, U: uncovered
                E: fatal error, X: syntax error, T: timed out, S: skipped, I: ignored

                .   (    1)
                .   (    2)
                .   (    3)

                TXT,
        ];
    }

    public function test_max_setting_resolves_the_terminal_width_only_once(): void
    {
        $totalMutations = 5;

        $terminal = $this->createMock(Terminal::class);
        $terminal
            ->expects($this->once())
            ->method('getWidth')
            ->willReturn(self::ANY_TERMINAL_WIDTH);

        $output = new BufferedOutput();
        $logger = new ConsoleDotLogger($output, 'max', $terminal);
        $logger->startEvaluation($totalMutations);

        for ($i = 0; $i < $totalMutations; ++$i) {
            $logger->finishEvaluationForMutation(
                $this->createMutantExecutionResultOfType(DetectionStatus::KILLED_BY_TESTS),
            );
        }
    }

    private function createMutantExecutionResultOfType(DetectionStatus $detectionStatus): MutantExecutionResult
    {
        return MutantExecutionResultBuilder::withMinimalTestData()
            ->withDetectionStatus($detectionStatus)
            ->build();
    }

    private function createTerminal(int $width): Terminal
    {
        $terminal = $this->createMock(Terminal::class);
        $terminal->method('getWidth')->willReturn($width);

        return $terminal;
    }
}
