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

namespace Infection\Tests\TestFramework\PhpUnit\Legacy;

use Infection\AbstractTestFramework\SyntaxErrorAware;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Mutant\DetectionStatus;
use Infection\TestFramework\PhpUnit\Legacy\LegacyMutantDetectionStatusResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(LegacyMutantDetectionStatusResolver::class)]
final class LegacyMutantDetectionStatusResolverTest extends TestCase
{
    public function test_it_reports_a_timed_out_mutant(): void
    {
        $resolver = new LegacyMutantDetectionStatusResolver($this->createStub(TestFrameworkAdapter::class), false);

        $this->assertSame(
            DetectionStatus::TIMED_OUT,
            $resolver->tryToResolve('', '', 0, true, null),
        );
    }

    public function test_it_reports_an_expected_timeout_as_skipped(): void
    {
        $adapter = $this->createMock(TestFrameworkAdapter::class);
        $adapter
            ->expects($this->never())
            ->method('testsPass')
        ;
        $resolver = new LegacyMutantDetectionStatusResolver($adapter, true);

        $this->assertSame(
            DetectionStatus::SKIPPED,
            $resolver->tryToResolve('', '', 143, true, null),
        );
    }

    public function test_expected_timeout_does_not_override_a_completed_evaluation(): void
    {
        $adapter = $this->createMock(TestFrameworkAdapter::class);
        $adapter
            ->expects($this->once())
            ->method('testsPass')
            ->with('passed')
            ->willReturn(true)
        ;
        $resolver = new LegacyMutantDetectionStatusResolver($adapter, true);

        $this->assertSame(
            DetectionStatus::ESCAPED,
            $resolver->tryToResolve('passed', '', 0, false, null),
        );
    }

    public function test_passing_output_with_a_nonzero_exit_code_does_not_escape(): void
    {
        $adapter = $this->createMock(TestFrameworkAdapter::class);
        $adapter
            ->expects($this->never())
            ->method('testsPass')
        ;
        $resolver = new LegacyMutantDetectionStatusResolver($adapter, false);

        $this->assertSame(
            DetectionStatus::KILLED_BY_TESTS,
            $resolver->tryToResolve('passed', '', 100, false, null),
        );
    }

    public function test_it_reports_a_process_error(): void
    {
        $resolver = new LegacyMutantDetectionStatusResolver($this->createStub(TestFrameworkAdapter::class), false);

        $this->assertSame(
            DetectionStatus::ERROR,
            $resolver->tryToResolve('', '', 101, false, null),
        );
    }

    public function test_it_reports_an_escaped_mutant_when_the_tests_pass(): void
    {
        $adapter = $this->createMock(TestFrameworkAdapter::class);
        $adapter
            ->expects($this->once())
            ->method('testsPass')
            ->with('output')
            ->willReturn(true)
        ;
        $resolver = new LegacyMutantDetectionStatusResolver($adapter, false);

        $this->assertSame(
            DetectionStatus::ESCAPED,
            $resolver->tryToResolve('output', '', 0, false, null),
        );
    }

    public function test_it_reports_a_syntax_error(): void
    {
        $adapter = $this->createSyntaxErrorAwareAdapter();
        $adapter
            ->method('testsPass')
            ->willReturn(false)
        ;
        $adapter
            ->expects($this->once())
            ->method('isSyntaxError')
            ->with('output')
            ->willReturn(true)
        ;
        $resolver = new LegacyMutantDetectionStatusResolver($adapter, false);

        $this->assertSame(
            DetectionStatus::SYNTAX_ERROR,
            $resolver->tryToResolve('output', '', 1, false, null),
        );
    }

    #[DataProvider('syntaxErrorProvider')]
    public function test_it_classifies_error_output_with_a_nonzero_exit_code(
        bool $syntaxError,
        DetectionStatus $expected,
    ): void {
        $adapter = $this->createSyntaxErrorAwareAdapter();
        $adapter
            ->expects($this->never())
            ->method('testsPass')
        ;
        $adapter
            ->expects($this->once())
            ->method('isSyntaxError')
            ->with('output')
            ->willReturn($syntaxError)
        ;
        $resolver = new LegacyMutantDetectionStatusResolver($adapter, false);

        $this->assertSame(
            $expected,
            $resolver->tryToResolve('output', 'stderr', 1, false, null),
        );
    }

    public static function syntaxErrorProvider(): iterable
    {
        yield 'syntax error' => [true, DetectionStatus::SYNTAX_ERROR];

        yield 'other failure' => [false, DetectionStatus::KILLED_BY_TESTS];
    }

    public function test_it_reports_a_mutant_killed_by_tests(): void
    {
        $adapter = $this->createStub(TestFrameworkAdapter::class);
        $adapter
            ->method('testsPass')
            ->willReturn(false)
        ;
        $resolver = new LegacyMutantDetectionStatusResolver($adapter, false);

        $this->assertSame(
            DetectionStatus::KILLED_BY_TESTS,
            $resolver->tryToResolve('output', '', 1, false, null),
        );
    }

    private function createSyntaxErrorAwareAdapter(): TestFrameworkAdapter&SyntaxErrorAware&MockObject
    {
        $adapter = $this->createMockForIntersectionOfInterfaces([
            TestFrameworkAdapter::class,
            SyntaxErrorAware::class,
        ]);

        $this->assertInstanceOf(TestFrameworkAdapter::class, $adapter);
        $this->assertInstanceOf(SyntaxErrorAware::class, $adapter);

        return $adapter;
    }
}
