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
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\Mutant;
use Infection\Mutant\TestSuiteMutantDetectionStatusResolver;
use Infection\Process\DryRunProcess;
use Infection\Process\MutantProcess;
use Infection\TestFramework\Legacy\LegacyMutantDetectionStatusResolver;
use Infection\Tests\Mutation\MutationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use function sprintf;

#[CoversClass(TestSuiteMutantDetectionStatusResolver::class)]
final class TestSuiteMutantDetectionStatusResolverTest extends TestCase
{
    private Stub&TestFrameworkAdapter $adapter;

    protected function setUp(): void
    {
        $this->adapter = $this->createStub(TestFrameworkAdapter::class);
    }

    #[DataProvider('detectionStatusProvider')]
    public function test_it_resolves_the_detection_status(int $exitCode, bool $testsPass, bool $covered, DetectionStatus $status): void
    {
        $this->adapter->method('testsPass')->willReturn($testsPass);
        $mutant = $this->createStub(Mutant::class);
        $mutant->method('isCoveredByTest')->willReturn($covered);
        $resolver = new TestSuiteMutantDetectionStatusResolver(new LegacyMutantDetectionStatusResolver($this->adapter), $mutant, 10.0);

        $this->assertSame($status, $resolver->resolve('test output', $exitCode, timedOut: !$covered));
    }

    public static function detectionStatusProvider(): iterable
    {
        yield 'uncovered mutant takes precedence over timeout' => [0, true, false, DetectionStatus::NOT_COVERED];

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
        $tests = [];

        foreach ($testTimes as $index => $testTime) {
            $tests[] = new TestLocation(
                sprintf('Foo%dTest::test_it_can_instantiate', $index),
                sprintf('/path/to/acme/Foo%dTest.php', $index),
                $testTime,
            );
        }

        $mutant = MutantBuilder::withMinimalTestData()
            ->withMutation(
                MutationBuilder::withMinimalTestData()
                    ->withTests($tests)
                    ->build(),
            )
            ->build()
        ;
        $resolver = new TestSuiteMutantDetectionStatusResolver(
            new LegacyMutantDetectionStatusResolver($this->adapter),
            $mutant,
            $processTimeout,
        );

        $this->assertSame($expected, $resolver->resolve('', null, timedOut: true));
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

    public function test_it_reports_a_dry_run_attempt_as_escaped(): void
    {
        $this->adapter->method('testsPass')->willReturnCallback(
            static fn (string $output): bool => $output === DryRunProcess::PASSING_TEST_OUTPUT,
        );
        $mutant = $this->createStub(Mutant::class);
        $mutant->method('isCoveredByTest')->willReturn(true);
        $resolver = new TestSuiteMutantDetectionStatusResolver(new LegacyMutantDetectionStatusResolver($this->adapter), $mutant, 10.0);
        $dryRunProcess = new DryRunProcess(['phpunit']);
        $process = new MutantProcess(
            $dryRunProcess,
            $mutant,
            'PHPUnit',
            $resolver,
            includeErrorOutput: false,
        );
        $dryRunProcess->start();
        $process->markAsFinished();

        $this->assertSame(DetectionStatus::ESCAPED, $process->getResult());
        $this->assertSame(DryRunProcess::PASSING_TEST_OUTPUT, $process->getResultOutput());
        $this->assertSame(0.0, $process->getRuntime());
    }
}
