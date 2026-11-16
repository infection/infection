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

namespace Infection\Tests\Reporter;

use function array_map;
use Infection\Framework\Str;
use Infection\Metrics\ResultsCollector;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\EvaluationAttemptResult;
use Infection\Mutant\EvaluationProcessDetails;
use Infection\Reporter\BaseTextFileReporter;
use Infection\Reporter\TextFileReporter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TextFileReporter::class)]
#[CoversClass(BaseTextFileReporter::class)]
final class TextFileReporterTest extends TestCase
{
    use CreateMetricsCalculator;
    use LineReporterAssertions;

    #[DataProvider('emptyMetricsProvider')]
    public function test_it_reports_results_in_a_text_file_when_there_is_no_mutation(
        bool $debugVerbosity,
        bool $onlyCoveredMode,
        bool $debugMode,
        string $expectedContents,
    ): void {
        $reporter = new TextFileReporter(
            new ResultsCollector(),
            $debugVerbosity,
            $onlyCoveredMode,
            $debugMode,
        );

        $this->assertReportedContentIs($expectedContents, $reporter);
    }

    #[DataProvider('completeMetricsProvider')]
    public function test_it_reports_results_in_a_text_file_when_there_are_mutations(
        bool $debugVerbosity,
        bool $onlyCoveredMode,
        bool $debugMode,
        string $expectedContents,
    ): void {
        $reporter = new TextFileReporter(
            self::createCompleteResultsCollector(),
            $debugVerbosity,
            $onlyCoveredMode,
            $debugMode,
        );

        $this->assertReportedContentIs($expectedContents, $reporter);
    }

    #[DataProvider('evaluationHistoryProvider')]
    public function test_it_reports_completed_evaluation_attempts(
        bool $debugVerbosity,
        bool $debugMode,
        bool $withTestAttempt,
        string $expectedSection,
    ): void {
        $reporter = new TextFileReporter(
            self::createEvaluationHistoryResultsCollector($withTestAttempt),
            $debugVerbosity,
            false,
            $debugMode,
        );

        $sections = array_map(Str::toUnixLineEndings(...), $reporter->getLines());

        $this->assertContains($expectedSection, $sections);
    }

    public static function evaluationHistoryProvider(): iterable
    {
        $prefix = <<<'TXT'
            Escaped mutants:
            ================

            1) src/Foo.php:10    [M] For_ [ID] abc123def456

            mutant diff
            TXT;

        yield 'commands and outputs from both attempts' => [
            true,
            true,
            true,
            $prefix . <<<'TXT'


                PHPUnit:
                $ vendor/bin/phpunit
                  PHPUnit output
                  next line

                PHPStan:
                $ vendor/bin/phpstan
                  PHPStan output
                TXT,
        ];

        yield 'outputs from both attempts without commands' => [
            true,
            false,
            true,
            $prefix . <<<'TXT'


                PHPUnit:
                  PHPUnit output
                  next line

                PHPStan:
                  PHPStan output
                TXT,
        ];

        yield 'commands from both attempts without outputs' => [
            false,
            true,
            true,
            $prefix . <<<'TXT'


                PHPUnit:
                $ vendor/bin/phpunit

                PHPStan:
                $ vendor/bin/phpstan
                TXT,
        ];

        yield 'no attempt details when neither flag is enabled' => [false, false, true, $prefix];

        yield 'single attempt preserves the existing format' => [
            true,
            true,
            false,
            $prefix . <<<'TXT'


                $ vendor/bin/phpstan
                  PHPStan output
                TXT,
        ];
    }

    /**
     * @param non-empty-list<EvaluationAttemptResult> $history
     */
    #[DataProvider('mixedEvaluationHistoryProvider')]
    public function test_it_reports_process_and_direct_observations_from_the_locked_history(
        array $history,
        bool $debugVerbosity,
        bool $debugMode,
        string $expectedDetails,
    ): void {
        // The final status is already locked as ESCAPED, even when the last
        // observation reports an error or a detection.
        $collector = self::createEvaluationHistoryResultsCollector(history: $history);
        $reporter = new TextFileReporter($collector, $debugVerbosity, false, $debugMode);
        $prefix = <<<'TXT'
            Escaped mutants:
            ================

            1) src/Foo.php:10    [M] For_ [ID] abc123def456

            mutant diff
            TXT;
        $expected = $prefix . $expectedDetails;
        $sections = array_map(Str::toUnixLineEndings(...), $reporter->getLines());

        $this->assertContains($expected, $sections);
        $this->assertSame($history, $collector->getEscapedExecutionResults()[0]->getEvaluationHistory());
        $this->assertSame($sections, array_map(Str::toUnixLineEndings(...), $reporter->getLines()));
    }

    public static function mixedEvaluationHistoryProvider(): iterable
    {
        $direct = new EvaluationAttemptResult('In-memory analyser', DetectionStatus::KILLED_BY_STATIC_ANALYSIS, "\r\nType mismatch \r\nnext line \r\n", null);
        $tests = new EvaluationAttemptResult('PHPUnit', DetectionStatus::ESCAPED, 'Tests pass', new EvaluationProcessDetails('vendor/bin/phpunit', 1.0));
        $analysis = new EvaluationAttemptResult('PHPStan', DetectionStatus::ERROR, 'Analysis failed', new EvaluationProcessDetails('vendor/bin/phpstan', 0.25));

        yield 'one direct observation has output without a command or label' => [
            [$direct],
            true,
            true,
            <<<'TXT'


                  Type mismatch
                  next line
                TXT,
        ];

        yield 'one direct observation has output with verbosity only' => [
            [$direct],
            true,
            false,
            <<<'TXT'


                  Type mismatch
                  next line
                TXT,
        ];

        yield 'one direct observation adds no empty command in debug-only mode' => [[$direct], false, true, ''];

        yield 'process then direct observation retains both outputs' => [
            [$tests, $direct],
            true,
            true,
            <<<'TXT'


                PHPUnit:
                $ vendor/bin/phpunit
                  Tests pass

                In-memory analyser:
                  Type mismatch
                  next line
                TXT,
        ];

        yield 'direct observation then process retains both outputs' => [
            [$direct, $analysis],
            true,
            true,
            <<<'TXT'


                In-memory analyser:
                  Type mismatch
                  next line

                PHPStan:
                $ vendor/bin/phpstan
                  Analysis failed
                TXT,
        ];

        yield 'mixed observations retain labels and real commands in debug-only mode' => [
            [$direct, $analysis],
            false,
            true,
            <<<'TXT'


                In-memory analyser:

                PHPStan:
                $ vendor/bin/phpstan
                TXT,
        ];

        yield 'mixed observations omit commands with verbosity only' => [
            [$tests, $direct],
            true,
            false,
            <<<'TXT'


                PHPUnit:
                  Tests pass

                In-memory analyser:
                  Type mismatch
                  next line
                TXT,
        ];

        yield 'direct observations sanitize labels and outputs' => [
            [
                new EvaluationAttemptResult("\r\nFirst analyser \r\n", DetectionStatus::ESCAPED, "\r\nNo issues \r\n", null),
                $direct,
            ],
            true,
            true,
            <<<'TXT'


                First analyser:
                  No issues

                In-memory analyser:
                  Type mismatch
                  next line
                TXT,
        ];

        yield 'retries retain repeated evaluator labels in execution order' => [
            [
                new EvaluationAttemptResult('PHPUnit', DetectionStatus::TIMED_OUT, 'First attempt timed out', new EvaluationProcessDetails('vendor/bin/phpunit --first', 1.0)),
                new EvaluationAttemptResult('PHPUnit', DetectionStatus::ESCAPED, 'Retry passed', new EvaluationProcessDetails('vendor/bin/phpunit --retry', 0.5)),
                $analysis,
            ],
            true,
            true,
            <<<'TXT'


                PHPUnit:
                $ vendor/bin/phpunit --first
                  First attempt timed out

                PHPUnit:
                $ vendor/bin/phpunit --retry
                  Retry passed

                PHPStan:
                $ vendor/bin/phpstan
                  Analysis failed
                TXT,
        ];

        yield 'mixed observations remain hidden when neither flag is enabled' => [[$tests, $direct], false, false, ''];
    }

    public static function emptyMetricsProvider(): iterable
    {
        yield 'no debug verbosity; no debug mode' => [
            false,
            false,
            false,
            <<<'TXT'
                Note: Pass `--log-verbosity=all` to log information about killed and errored mutants.
                Note: Pass `--debug` to log test-framework output.

                Escaped mutants:
                ================

                Timed Out mutants:
                ==================

                Skipped mutants:
                ================

                Not Covered mutants:
                ====================

                TXT,
        ];

        yield 'debug verbosity; no debug mode' => [
            true,
            false,
            false,
            <<<'TXT'
                Note: Pass `--debug` to log test-framework output.

                Escaped mutants:
                ================

                Timed Out mutants:
                ==================

                Skipped mutants:
                ================

                Killed by Test Framework mutants:
                =================================

                Killed by Static Analysis mutants:
                ==================================

                Errors mutants:
                ===============

                Syntax Errors mutants:
                ======================

                Not Covered mutants:
                ====================

                TXT,
        ];

        yield 'no debug verbosity; debug mode' => [
            false,
            false,
            true,
            <<<'TXT'
                Note: Pass `--log-verbosity=all` to log information about killed and errored mutants.

                Escaped mutants:
                ================

                Timed Out mutants:
                ==================

                Skipped mutants:
                ================

                Not Covered mutants:
                ====================

                TXT,
        ];

        yield 'debug verbosity; debug mode' => [
            true,
            false,
            true,
            <<<'TXT'
                Escaped mutants:
                ================

                Timed Out mutants:
                ==================

                Skipped mutants:
                ================

                Killed by Test Framework mutants:
                =================================

                Killed by Static Analysis mutants:
                ==================================

                Errors mutants:
                ===============

                Syntax Errors mutants:
                ======================

                Not Covered mutants:
                ====================

                TXT,
        ];

        yield 'no debug verbosity; no debug mode; only covered' => [
            false,
            true,
            false,
            <<<'TXT'
                Note: Pass `--log-verbosity=all` to log information about killed and errored mutants.
                Note: Pass `--debug` to log test-framework output.

                Escaped mutants:
                ================

                Timed Out mutants:
                ==================

                Skipped mutants:
                ================

                TXT,
        ];

        yield 'debug verbosity; no debug mode; only covered' => [
            true,
            true,
            false,
            <<<'TXT'
                Note: Pass `--debug` to log test-framework output.

                Escaped mutants:
                ================

                Timed Out mutants:
                ==================

                Skipped mutants:
                ================

                Killed by Test Framework mutants:
                =================================

                Killed by Static Analysis mutants:
                ==================================

                Errors mutants:
                ===============

                Syntax Errors mutants:
                ======================

                TXT,
        ];

        yield 'no debug verbosity; debug mode; only covered' => [
            false,
            true,
            true,
            <<<'TXT'
                Note: Pass `--log-verbosity=all` to log information about killed and errored mutants.

                Escaped mutants:
                ================

                Timed Out mutants:
                ==================

                Skipped mutants:
                ================

                TXT,
        ];

        yield 'debug verbosity; debug mode; only covered' => [
            true,
            true,
            true,
            <<<'TXT'
                Escaped mutants:
                ================

                Timed Out mutants:
                ==================

                Skipped mutants:
                ================

                Killed by Test Framework mutants:
                =================================

                Killed by Static Analysis mutants:
                ==================================

                Errors mutants:
                ===============

                Syntax Errors mutants:
                ======================

                TXT,
        ];
    }

    public static function completeMetricsProvider(): iterable
    {
        yield 'no debug verbosity; no debug mode' => [
            false,
            false,
            false,
            <<<'TXT'
                Note: Pass `--log-verbosity=all` to log information about killed and errored mutants.
                Note: Pass `--debug` to log test-framework output.

                Escaped mutants:
                ================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'escaped#1';


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'escaped#0';


                Timed Out mutants:
                ==================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'timedOut#1';


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'timedOut#0';


                Skipped mutants:
                ================

                1) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'skipped#0';


                2) foo/bar:10    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'skipped#1';


                Not Covered mutants:
                ====================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'notCovered#1';


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'notCovered#0';

                TXT,
        ];

        yield 'debug verbosity; no debug mode' => [
            true,
            false,
            false,
            <<<'TXT'
                Note: Pass `--debug` to log test-framework output.

                Escaped mutants:
                ================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'escaped#1';

                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'escaped#0';

                  process output


                Timed Out mutants:
                ==================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'timedOut#1';

                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'timedOut#0';

                  process output


                Skipped mutants:
                ================

                1) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'skipped#0';

                  process output


                2) foo/bar:10    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'skipped#1';

                  process output


                Killed by Test Framework mutants:
                =================================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'killed#1';

                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'killed#0';

                  process output


                Killed by Static Analysis mutants:
                ==================================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'killed by SA#0';

                  process output


                Errors mutants:
                ===============

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'error#1';

                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'error#0';

                  process output


                Syntax Errors mutants:
                ======================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'syntaxError#1';

                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'syntaxError#0';

                  process output


                Not Covered mutants:
                ====================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'notCovered#1';

                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'notCovered#0';

                  process output

                TXT,
        ];

        yield 'no debug verbosity; debug mode' => [
            false,
            false,
            true,
            <<<'TXT'
                Note: Pass `--log-verbosity=all` to log information about killed and errored mutants.

                Escaped mutants:
                ================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'escaped#1';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'escaped#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"


                Timed Out mutants:
                ==================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'timedOut#1';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'timedOut#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"


                Skipped mutants:
                ================

                1) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'skipped#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"


                2) foo/bar:10    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'skipped#1';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"


                Not Covered mutants:
                ====================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'notCovered#1';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'notCovered#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"

                TXT,
        ];

        yield 'debug verbosity; debug mode' => [
            true,
            false,
            true,
            <<<'TXT'
                Escaped mutants:
                ================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'escaped#1';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'escaped#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                Timed Out mutants:
                ==================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'timedOut#1';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'timedOut#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                Skipped mutants:
                ================

                1) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'skipped#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                2) foo/bar:10    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'skipped#1';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                Killed by Test Framework mutants:
                =================================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'killed#1';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'killed#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                Killed by Static Analysis mutants:
                ==================================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'killed by SA#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                Errors mutants:
                ===============

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'error#1';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'error#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                Syntax Errors mutants:
                ======================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'syntaxError#1';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'syntaxError#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                Not Covered mutants:
                ====================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'notCovered#1';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'notCovered#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output

                TXT,
        ];

        yield 'no debug verbosity; no debug mode; only covered' => [
            false,
            true,
            false,
            <<<'TXT'
                Note: Pass `--log-verbosity=all` to log information about killed and errored mutants.
                Note: Pass `--debug` to log test-framework output.

                Escaped mutants:
                ================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'escaped#1';


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'escaped#0';


                Timed Out mutants:
                ==================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'timedOut#1';


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'timedOut#0';


                Skipped mutants:
                ================

                1) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'skipped#0';


                2) foo/bar:10    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'skipped#1';

                TXT,
        ];

        yield 'debug verbosity; no debug mode; only covered' => [
            true,
            true,
            false,
            <<<'TXT'
                Note: Pass `--debug` to log test-framework output.

                Escaped mutants:
                ================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'escaped#1';

                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'escaped#0';

                  process output


                Timed Out mutants:
                ==================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'timedOut#1';

                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'timedOut#0';

                  process output


                Skipped mutants:
                ================

                1) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'skipped#0';

                  process output


                2) foo/bar:10    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'skipped#1';

                  process output


                Killed by Test Framework mutants:
                =================================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'killed#1';

                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'killed#0';

                  process output


                Killed by Static Analysis mutants:
                ==================================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'killed by SA#0';

                  process output


                Errors mutants:
                ===============

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'error#1';

                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'error#0';

                  process output


                Syntax Errors mutants:
                ======================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'syntaxError#1';

                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'syntaxError#0';

                  process output

                TXT,
        ];

        yield 'no debug verbosity; debug mode; only covered' => [
            false,
            true,
            true,
            <<<'TXT'
                Note: Pass `--log-verbosity=all` to log information about killed and errored mutants.

                Escaped mutants:
                ================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'escaped#1';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'escaped#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"


                Timed Out mutants:
                ==================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'timedOut#1';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'timedOut#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"


                Skipped mutants:
                ================

                1) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'skipped#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"


                2) foo/bar:10    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'skipped#1';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"

                TXT,
        ];

        yield 'debug verbosity; debug mode; only covered' => [
            true,
            true,
            true,
            <<<'TXT'
                Escaped mutants:
                ================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'escaped#1';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'escaped#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                Timed Out mutants:
                ==================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'timedOut#1';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'timedOut#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                Skipped mutants:
                ================

                1) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'skipped#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                2) foo/bar:10    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'skipped#1';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                Killed by Test Framework mutants:
                =================================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'killed#1';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'killed#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                Killed by Static Analysis mutants:
                ==================================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'killed by SA#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                Errors mutants:
                ===============

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'error#1';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'error#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                Syntax Errors mutants:
                ======================

                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'syntaxError#1';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'syntaxError#0';

                $ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
                  process output

                TXT,
        ];
    }
}
