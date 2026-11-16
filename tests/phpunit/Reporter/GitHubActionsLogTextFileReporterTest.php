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
use Infection\Reporter\GitHubActionsLogTextFileReporter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(GitHubActionsLogTextFileReporter::class)]
#[CoversClass(BaseTextFileReporter::class)]
final class GitHubActionsLogTextFileReporterTest extends TestCase
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
        $reporter = new GitHubActionsLogTextFileReporter(
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
        $reporter = new GitHubActionsLogTextFileReporter(
            self::createCompleteResultsCollector(),
            $debugVerbosity,
            $onlyCoveredMode,
            $debugMode,
        );

        $this->assertReportedContentIs($expectedContents, $reporter);
    }

    public function test_it_groups_both_evaluators_outputs_when_static_analysis_kills_the_mutant(): void
    {
        $reporter = new GitHubActionsLogTextFileReporter(
            self::createEvaluationHistoryResultsCollector(status: DetectionStatus::KILLED_BY_STATIC_ANALYSIS),
            true,
            false,
            true,
        );
        $expected = <<<'TXT'
            ::group::Killed by Static Analysis mutants
            1) src/Foo.php:10    [M] For_ [ID] abc123def456

            mutant diff

            PHPUnit:
            $ vendor/bin/phpunit
              PHPUnit output
              next line

            PHPStan:
            $ vendor/bin/phpstan
              PHPStan output
            ::endgroup::

            TXT;

        $sections = array_map(Str::toUnixLineEndings(...), $reporter->getLines());

        $this->assertContains($expected, $sections);
    }

    #[DataProvider('directEvaluationHistoryProvider')]
    public function test_it_groups_mixed_observations_by_the_locked_final_status(
        bool $debugVerbosity,
        bool $debugMode,
        string $expectedDetails,
    ): void {
        $history = [
            new EvaluationAttemptResult('PHPUnit', DetectionStatus::ESCAPED, 'Tests pass', new EvaluationProcessDetails('vendor/bin/phpunit', 1.0)),
            new EvaluationAttemptResult("\r\nIn-memory analyser \r\n", DetectionStatus::ERROR, "\r\nAnalysis failed \r\n", null),
        ];
        $collector = self::createEvaluationHistoryResultsCollector(history: $history);
        $reporter = new GitHubActionsLogTextFileReporter($collector, $debugVerbosity, false, $debugMode);
        $prefix = <<<'TXT'
            ::group::Escaped mutants
            1) src/Foo.php:10    [M] For_ [ID] abc123def456

            mutant diff
            TXT;
        $expected = $prefix . $expectedDetails . "\n::endgroup::\n";
        $sections = array_map(Str::toUnixLineEndings(...), $reporter->getLines());

        $this->assertContains($expected, $sections);
        $this->assertSame($history, $collector->getEscapedExecutionResults()[0]->getEvaluationHistory());
    }

    public static function directEvaluationHistoryProvider(): iterable
    {
        yield 'commands and outputs include direct evidence without a fabricated command' => [
            true,
            true,
            <<<'TXT'


                PHPUnit:
                $ vendor/bin/phpunit
                  Tests pass

                In-memory analyser:
                  Analysis failed
                TXT,
        ];

        yield 'verbosity retains both outputs without commands' => [
            true,
            false,
            <<<'TXT'


                PHPUnit:
                  Tests pass

                In-memory analyser:
                  Analysis failed
                TXT,
        ];

        yield 'debug mode retains evaluator labels and only the real command' => [
            false,
            true,
            <<<'TXT'


                PHPUnit:
                $ vendor/bin/phpunit

                In-memory analyser:
                TXT,
        ];

        yield 'neither flag omits evaluator details' => [false, false, ''];
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

                0 Escaped mutants

                0 Timed Out mutants

                0 Skipped mutants

                0 Not Covered mutants

                TXT,
        ];

        yield 'debug verbosity; no debug mode' => [
            true,
            false,
            false,
            <<<'TXT'
                Note: Pass `--debug` to log test-framework output.

                0 Escaped mutants

                0 Timed Out mutants

                0 Skipped mutants

                0 Killed by Test Framework mutants

                0 Killed by Static Analysis mutants

                0 Errors mutants

                0 Syntax Errors mutants

                0 Not Covered mutants

                TXT,
        ];

        yield 'debug verbosity; debug mode' => [
            true,
            false,
            true,
            <<<'TXT'
                0 Escaped mutants

                0 Timed Out mutants

                0 Skipped mutants

                0 Killed by Test Framework mutants

                0 Killed by Static Analysis mutants

                0 Errors mutants

                0 Syntax Errors mutants

                0 Not Covered mutants

                TXT,
        ];

        yield 'no debug verbosity; no debug mode; only covered' => [
            false,
            true,
            false,
            <<<'TXT'
                Note: Pass `--log-verbosity=all` to log information about killed and errored mutants.
                Note: Pass `--debug` to log test-framework output.

                0 Escaped mutants

                0 Timed Out mutants

                0 Skipped mutants

                TXT,
        ];

        yield 'debug verbosity; no debug mode; only covered' => [
            true,
            true,
            false,
            <<<'TXT'
                Note: Pass `--debug` to log test-framework output.

                0 Escaped mutants

                0 Timed Out mutants

                0 Skipped mutants

                0 Killed by Test Framework mutants

                0 Killed by Static Analysis mutants

                0 Errors mutants

                0 Syntax Errors mutants

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

                ::group::Escaped mutants
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
                ::endgroup::

                ::group::Timed Out mutants
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
                ::endgroup::

                ::group::Skipped mutants
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
                ::endgroup::

                ::group::Not Covered mutants
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
                ::endgroup::


                TXT,
        ];
    }
}
