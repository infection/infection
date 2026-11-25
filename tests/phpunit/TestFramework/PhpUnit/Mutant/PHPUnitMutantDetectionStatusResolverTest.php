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

namespace Infection\Tests\TestFramework\PhpUnit\Mutant;

use Infection\Mutant\DetectionStatus;
use Infection\TestFramework\PhpUnit\Mutant\PHPUnitMutantDetectionStatusResolver;
use Infection\Tests\TestFramework\Mutant\DetectionStatusScenario;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(PHPUnitMutantDetectionStatusResolver::class)]
final class PHPUnitMutantDetectionStatusResolverTest extends TestCase
{
    #[DataProvider('processOutcomesProvider')]
    public function test_it_resolves_the_process_outcome(DetectionStatusScenario $scenario): void
    {
        $resolver = new PHPUnitMutantDetectionStatusResolver();

        $actual = $resolver->resolve($scenario->stdout, $scenario->exitCode, $scenario->timedOut);

        $this->assertSame($scenario->expected, $actual);
    }

    public static function processOutcomesProvider(): iterable
    {
        $baseScenario = DetectionStatusScenario::withTestData();

        yield 'timeout takes precedence over a process error' => [
            $baseScenario
                ->withExitCode(101)
                ->withTimedOut(true)
                ->withExpected(DetectionStatus::TIMED_OUT),
        ];

        yield 'process error takes precedence over a syntax error' => [
            $baseScenario
                ->withStdout('ParseError: syntax error')
                ->withExitCode(101)
                ->withExpected(DetectionStatus::ERROR),
        ];

        yield 'successful test output' => [
            $baseScenario
                ->withStdout('OK (5 tests, 3 assertions)')
                ->withExpected(DetectionStatus::ESCAPED),
        ];

        yield 'successful test output with informational messages' => [
            $baseScenario
                ->withStdout('OK, but incomplete, skipped, or risky tests!')
                ->withExpected(DetectionStatus::ESCAPED),
        ];

        yield 'warnings without failures' => [
            $baseScenario
                ->withStdout('WARNINGS!')
                ->withExpected(DetectionStatus::ESCAPED),
        ];

        yield 'no tests executed with a different case' => [
            $baseScenario
                ->withStdout('NO TESTS EXECUTED!')
                ->withExpected(DetectionStatus::ESCAPED),
        ];

        yield 'failure takes precedence over a warning' => [
            $baseScenario
                ->withStdout("WARNINGS!\nFAILURES!")
                ->withExpected(DetectionStatus::KILLED_BY_TESTS),
        ];

        yield 'error takes precedence over a warning' => [
            $baseScenario
                ->withStdout("WARNINGS!\nERRORS!")
                ->withExpected(DetectionStatus::KILLED_BY_TESTS),
        ];

        yield 'passing output with a nonzero exit code' => [
            $baseScenario
                ->withStdout('OK (5 tests, 3 assertions)')
                ->withExitCode(100)
                ->withExpected(DetectionStatus::KILLED_BY_TESTS),
        ];

        yield 'passing output without an exit code' => [
            $baseScenario
                ->withStdout('OK (5 tests, 3 assertions)')
                ->withExitCode(null)
                ->withExpected(DetectionStatus::KILLED_BY_TESTS),
        ];

        yield 'syntax error with a nonzero exit code' => [
            $baseScenario
                ->withStdout('ParseError: syntax error')
                ->withExitCode(1)
                ->withExpected(DetectionStatus::SYNTAX_ERROR),
        ];

        yield 'syntax error with a different case and without an exit code' => [
            $baseScenario
                ->withStdout('ParseError: Syntax error')
                ->withExitCode(null)
                ->withExpected(DetectionStatus::SYNTAX_ERROR),
        ];

        yield 'unrecognised output with a successful exit code' => [
            $baseScenario
                ->withStdout('unexpected output')
                ->withExpected(DetectionStatus::KILLED_BY_TESTS),
        ];
    }
}
