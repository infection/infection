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

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\AbstractTestFramework\SyntaxErrorAware;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\EvaluationAttemptResult;
use Infection\Mutant\EvaluationProcessDetails;
use Infection\Mutant\Mutant;
use Infection\Mutant\TestFrameworkEvaluationAttemptResultFactory;
use Infection\Process\MutantProcess;
use Infection\Tests\Mutation\MutationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use function sprintf;
use Symfony\Component\Process\Process;

#[CoversClass(TestFrameworkEvaluationAttemptResultFactory::class)]
final class TestFrameworkEvaluationAttemptResultFactoryTest extends TestCase
{
    private Stub&TestFrameworkAdapter $adapter;

    private TestFrameworkEvaluationAttemptResultFactory $resultFactory;

    protected function setUp(): void
    {
        $this->adapter = $this->createStub(TestFrameworkAdapter::class);
        $this->adapter->method('getName')->willReturn('PHPUnit');
        $this->resultFactory = new TestFrameworkEvaluationAttemptResultFactory($this->adapter);
    }

    #[DataProvider('detectionStatusProvider')]
    public function test_it_classifies_the_attempt_and_retains_its_evidence(int $exitCode, bool $testsPass, bool $covered, DetectionStatus $status): void
    {
        $this->adapter->method('testsPass')->willReturn($testsPass);
        $process = $this->createStub(Process::class);
        $process->method('isTerminated')->willReturn(true);
        $process->method('getCommandLine')->willReturn('phpunit');
        $process->method('getOutput')->willReturn('test output');
        $process->method('getExitCode')->willReturn($exitCode);
        $process->method('getStartTime')->willReturn(10.0);
        $mutant = $this->createStub(Mutant::class);
        $mutant->method('isCoveredByTest')->willReturn($covered);
        $mutantProcess = $this->createStub(MutantProcess::class);
        $mutantProcess->method('getProcess')->willReturn($process);
        $mutantProcess->method('getMutant')->willReturn($mutant);
        $mutantProcess->method('getRuntime')->willReturn(1.5);
        $expected = new EvaluationAttemptResult(
            'PHPUnit',
            $status,
            'test output',
            new EvaluationProcessDetails('phpunit', 1.5),
        );

        $actual = $this->resultFactory->createFromProcess($mutantProcess);

        $this->assertEquals($expected, $actual);
    }

    public static function detectionStatusProvider(): iterable
    {
        yield 'uncovered mutant' => [0, true, false, DetectionStatus::NOT_COVERED];

        yield 'tests pass' => [0, true, true, DetectionStatus::ESCAPED];

        yield 'failure in output despite successful exit' => [0, false, true, DetectionStatus::KILLED_BY_TESTS];

        // A bridge may fail the process for deprecations even when tests pass (#1620).
        yield 'passing output with unsuccessful exit' => [1, true, true, DetectionStatus::KILLED_BY_TESTS];

        yield 'tests fail' => [1, false, true, DetectionStatus::KILLED_BY_TESTS];

        yield 'maximum test failure exit code' => [100, false, true, DetectionStatus::KILLED_BY_TESTS];

        yield 'first process error exit code' => [101, false, true, DetectionStatus::ERROR];

        yield 'process fails' => [152, false, true, DetectionStatus::ERROR];
    }

    /**
     * @param list<float> $testTimes
     */
    #[DataProvider('timedOutProcessProvider')]
    public function test_it_distinguishes_expected_timeouts_from_unexpected_ones(
        ?float $processTimeout,
        array $testTimes,
        DetectionStatus $expected,
    ): void {
        $processMock = $this->createStub(Process::class);
        $processMock
            ->method('isTerminated')
            ->willReturn(true)
        ;
        $processMock
            ->method('getOutput')
            ->willReturn('')
        ;
        $processMock
            ->method('getTimeout')
            ->willReturn($processTimeout)
        ;

        $tests = [];

        foreach ($testTimes as $index => $testTime) {
            $tests[] = new TestLocation(
                sprintf('Foo%dTest::test_it_can_instantiate', $index),
                sprintf('/path/to/acme/Foo%dTest.php', $index),
                $testTime,
            );
        }

        $mutantProcess = new MutantProcess(
            $processMock,
            MutantBuilder::withMinimalTestData()
                ->withMutation(
                    MutationBuilder::withMinimalTestData()
                        ->withTests($tests)
                        ->build(),
                )
                ->build(),
            $this->resultFactory,
        );
        $mutantProcess->markAsTimedOut();

        $this->assertSame(
            $expected,
            $this->resultFactory->createFromProcess($mutantProcess)->detectionStatus,
        );
    }

    public static function timedOutProcessProvider(): iterable
    {
        yield 'no process timeout' => [
            null,
            [6.0, 6.0],
            DetectionStatus::TIMED_OUT,
        ];

        yield 'tests are expected to fit in the timeout' => [
            10.0,
            [4.0, 5.0],
            DetectionStatus::TIMED_OUT,
        ];

        yield 'tests are expected to take exactly the timeout' => [
            10.0,
            [4.0, 6.0],
            DetectionStatus::SKIPPED,
        ];

        yield 'tests are expected to exceed the timeout' => [
            10.0,
            [6.0, 6.0],
            DetectionStatus::SKIPPED,
        ];
    }

    public function test_it_recognizes_syntax_errors_when_the_framework_supports_it(): void
    {
        /**
         * @var Stub&TestFrameworkAdapter&SyntaxErrorAware $adapter
         */
        $adapter = $this->createStubForIntersectionOfInterfaces([TestFrameworkAdapter::class, SyntaxErrorAware::class]);
        $adapter->method('getName')->willReturn('PHPUnit');
        $adapter->method('testsPass')->willReturn(false);
        $adapter->method('isSyntaxError')->willReturn(true);
        $process = $this->createStub(Process::class);
        $process->method('isTerminated')->willReturn(true);
        $process->method('getOutput')->willReturn('syntax error');
        $process->method('getExitCode')->willReturn(1);
        $factory = new TestFrameworkEvaluationAttemptResultFactory($adapter);
        $mutantProcess = new MutantProcess($process, MutantBuilder::withCompleteTestData()->build(), $factory);

        $this->assertSame(DetectionStatus::SYNTAX_ERROR, $factory->createFromProcess($mutantProcess)->detectionStatus);
    }
}
