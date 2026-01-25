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

namespace Infection\Tests\Logger\MutationAnalysis\TeamCity;

use Infection\Event\MutantProcessWasFinished;
use Infection\Event\MutationTestingWasFinished;
use Infection\Event\MutationTestingWasStarted;
use Infection\Logger\MutationAnalysis\TeamCity\TeamCity;
use Infection\Logger\MutationAnalysis\TeamCity\TeamcitySubscriber;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutator\Loop\For_;
use Infection\Process\Runner\ProcessRunner;
use Infection\Testing\MutatorName;
use function Later\now;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function substr_count;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(TeamcitySubscriber::class)]
final class TeamcitySubscriberTest extends TestCase
{
    private BufferedOutput $output;

    private TeamcitySubscriber $subscriber;

    protected function setUp(): void
    {
        $this->output = new BufferedOutput();
        $this->subscriber = new TeamcitySubscriber(
            $this->output,
            new TeamCity(),
        );
    }

    public function test_it_outputs_test_count_on_mutation_testing_started(): void
    {
        $processRunner = $this->createMock(ProcessRunner::class);
        $event = new MutationTestingWasStarted(42, $processRunner);

        $this->subscriber->onMutationTestingWasStarted($event);

        $output = $this->output->fetch();

        $this->assertStringContainsString("##teamcity[testCount count='42'", $output);
    }

    public function test_it_outputs_test_suite_hierarchy_for_file_path(): void
    {
        $result = $this->createMutantResult(
            'src/Foo/Bar.php',
            DetectionStatus::KILLED_BY_TESTS,
        );

        $this->subscriber->onMutantProcessWasFinished(
            new MutantProcessWasFinished($result),
        );

        $output = $this->output->fetch();

        $this->assertStringContainsString("##teamcity[testSuiteStarted name='src'", $output);
        $this->assertStringContainsString("##teamcity[testSuiteStarted name='Foo'", $output);
        $this->assertStringContainsString("##teamcity[testSuiteStarted name='Bar.php'", $output);
    }

    public function test_it_closes_all_suites_on_mutation_testing_finished(): void
    {
        $result = $this->createMutantResult(
            'src/Foo/Bar.php',
            DetectionStatus::KILLED_BY_TESTS,
        );

        $this->subscriber->onMutantProcessWasFinished(
            new MutantProcessWasFinished($result),
        );

        $this->output->fetch(); // Clear

        $this->subscriber->onMutationTestingWasFinished(
            new MutationTestingWasFinished(),
        );

        $output = $this->output->fetch();

        $this->assertStringContainsString("##teamcity[testSuiteFinished name='Bar.php'", $output);
        $this->assertStringContainsString("##teamcity[testSuiteFinished name='Foo'", $output);
        $this->assertStringContainsString("##teamcity[testSuiteFinished name='src'", $output);
    }

    public function test_it_reuses_common_path_prefix(): void
    {
        $result1 = $this->createMutantResult(
            'src/Foo/Bar.php',
            DetectionStatus::KILLED_BY_TESTS,
        );
        $result2 = $this->createMutantResult(
            'src/Foo/Baz.php',
            DetectionStatus::KILLED_BY_TESTS,
        );

        $this->subscriber->onMutantProcessWasFinished(
            new MutantProcessWasFinished($result1),
        );
        $this->subscriber->onMutantProcessWasFinished(
            new MutantProcessWasFinished($result2),
        );

        $output = $this->output->fetch();

        // src and Foo should only be started once
        $this->assertSame(1, substr_count($output, "name='src'"));
        $this->assertSame(1, substr_count($output, "name='Foo'"));

        // Bar.php and Baz.php should both be started
        $this->assertStringContainsString("testSuiteStarted name='Bar.php'", $output);
        $this->assertStringContainsString("testSuiteStarted name='Baz.php'", $output);

        // Bar.php should be finished before Baz.php starts
        $this->assertStringContainsString("testSuiteFinished name='Bar.php'", $output);
    }

    #[DataProvider('mutantStatusProvider')]
    public function test_it_outputs_correct_test_result_for_status(
        DetectionStatus $status,
        string $expectedContains,
    ): void {
        $result = $this->createMutantResult(
            'src/Foo.php',
            $status,
        );

        $this->subscriber->onMutantProcessWasFinished(
            new MutantProcessWasFinished($result),
        );

        $output = $this->output->fetch();

        $this->assertStringContainsString('##teamcity[testStarted', $output);
        $this->assertStringContainsString($expectedContains, $output);
        $this->assertStringContainsString('##teamcity[testFinished', $output);
    }

    public static function mutantStatusProvider(): iterable
    {
        yield 'killed by tests' => [
            DetectionStatus::KILLED_BY_TESTS,
            '##teamcity[testFinished',
        ];

        yield 'killed by static analysis' => [
            DetectionStatus::KILLED_BY_STATIC_ANALYSIS,
            '##teamcity[testFinished',
        ];

        yield 'escaped' => [
            DetectionStatus::ESCAPED,
            '##teamcity[testFailed',
        ];

        yield 'timed out' => [
            DetectionStatus::TIMED_OUT,
            '##teamcity[testFailed',
        ];

        yield 'error' => [
            DetectionStatus::ERROR,
            '##teamcity[testFailed',
        ];

        yield 'syntax error' => [
            DetectionStatus::SYNTAX_ERROR,
            '##teamcity[testFailed',
        ];

        yield 'skipped' => [
            DetectionStatus::SKIPPED,
            '##teamcity[testIgnored',
        ];

        yield 'not covered' => [
            DetectionStatus::NOT_COVERED,
            '##teamcity[testIgnored',
        ];

        yield 'ignored' => [
            DetectionStatus::IGNORED,
            '##teamcity[testIgnored',
        ];
    }

    public function test_escaped_mutant_includes_diff_in_details(): void
    {
        $result = $this->createMutantResult(
            'src/Foo.php',
            DetectionStatus::ESCAPED,
        );

        $this->subscriber->onMutantProcessWasFinished(
            new MutantProcessWasFinished($result),
        );

        $output = $this->output->fetch();

        $this->assertStringContainsString("message='Mutant escaped'", $output);
        $this->assertStringContainsString("details='", $output);
    }

    public function test_test_name_includes_mutator_line_and_hash(): void
    {
        $result = $this->createMutantResult(
            'src/Foo.php',
            DetectionStatus::KILLED_BY_TESTS,
        );

        $this->subscriber->onMutantProcessWasFinished(
            new MutantProcessWasFinished($result),
        );

        $output = $this->output->fetch();

        // Test name should include mutator name, line number, and hash
        $this->assertStringContainsString('For_ (L10) abc123', $output);
    }

    private function createMutantResult(
        string $filePath,
        DetectionStatus $status,
    ): MutantExecutionResult {
        return new MutantExecutionResult(
            'bin/phpunit --filter "FooTest"',
            'process output',
            $status,
            now('--- Original\n+++ New\n- $a = 1;\n+ $a = 2;'),
            'abc123',
            For_::class,
            MutatorName::getName(For_::class),
            $filePath,
            10,
            15,
            100,
            150,
            now('<?php $a = 1;'),
            now('<?php $a = 2;'),
            [],
            0.5,
        );
    }
}
