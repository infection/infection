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

namespace Infection\Tests\Process;

use Infection\Mutant\DetectionStatus;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessContainer;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MutantProcessContainer::class)]
final class MutantProcessContainerTest extends TestCase
{
    public function test_it_has_no_next_process_without_a_follow_up_factory(): void
    {
        $process = $this->createMock(MutantProcess::class);
        $process
            ->expects($this->never())
            ->method('getMutantExecutionResult')
        ;

        $container = MutantProcessContainer::from(static fn (): MutantProcess => $process);

        $this->assertSame($process, $container->getCurrent());
        $this->assertFalse($container->hasNext());
    }

    #[DataProvider('detectionStatusProvider')]
    public function test_only_escaped_mutants_have_a_follow_up_process(DetectionStatus $status, bool $hasNext): void
    {
        $process = $this->createStub(MutantProcess::class);
        $result = MutantExecutionResultBuilder::withMinimalTestData()
            ->withDetectionStatus($status)
            ->build()
        ;
        $process
            ->method('getMutantExecutionResult')
            ->willReturn($result)
        ;

        $container = MutantProcessContainer::from(static fn (): MutantProcess => $process)
            ->append(function (): MutantProcess {
                $this->fail('Checking for a follow-up must not create its process.');
            })
        ;

        $this->assertSame($hasNext, $container->hasNext());
    }

    public function test_it_creates_each_process_lazily_once_and_advances_in_order(): void
    {
        $first = $this->createStub(MutantProcess::class);
        $second = $this->createStub(MutantProcess::class);
        $third = $this->createStub(MutantProcess::class);
        $escaped = MutantExecutionResultBuilder::withMinimalTestData()
            ->withDetectionStatus(DetectionStatus::ESCAPED)
            ->build()
        ;
        $first
            ->method('getMutantExecutionResult')
            ->willReturn($escaped)
        ;
        $second
            ->method('getMutantExecutionResult')
            ->willReturn($escaped)
        ;
        $killed = MutantExecutionResultBuilder::withMinimalTestData()
            ->withDetectionStatus(DetectionStatus::KILLED_BY_STATIC_ANALYSIS)
            ->build()
        ;
        $third
            ->method('getMutantExecutionResult')
            ->willReturn($killed)
        ;
        $calls = new class {
            public int $first = 0;

            public int $second = 0;
        };

        $container = MutantProcessContainer::from(static function () use ($first, $calls): MutantProcess {
            ++$calls->first;

            return $first;
        });
        $appended = $container->append(static function () use ($second, $calls): MutantProcess {
            ++$calls->second;

            return $second;
        });
        $container
            ->append(static fn (): MutantProcess => $third)
            ->append(function (): MutantProcess {
                $this->fail('A killed mutant must not advance to the remaining process.');
            })
        ;

        $this->assertSame($container, $appended);
        $this->assertSame(0, $calls->first);
        $this->assertSame(0, $calls->second);
        $this->assertSame($first, $container->getCurrent());
        $this->assertSame($first, $container->getCurrent());
        $this->assertTrue($container->hasNext());
        $this->assertSame(1, $calls->first);
        $this->assertSame(0, $calls->second);

        $this->assertSame($second, $container->createNext());
        $this->assertSame($second, $container->getCurrent());
        $this->assertSame(1, $calls->second);
        $this->assertTrue($container->hasNext());

        $this->assertSame($third, $container->createNext());
        $this->assertSame($third, $container->getCurrent());
        $this->assertFalse($container->hasNext());
    }

    public static function detectionStatusProvider(): iterable
    {
        yield 'escaped' => [DetectionStatus::ESCAPED, true];

        yield 'killed by tests' => [DetectionStatus::KILLED_BY_TESTS, false];

        yield 'killed by static analysis' => [DetectionStatus::KILLED_BY_STATIC_ANALYSIS, false];

        yield 'error' => [DetectionStatus::ERROR, false];

        yield 'timeout' => [DetectionStatus::TIMED_OUT, false];

        yield 'syntax error' => [DetectionStatus::SYNTAX_ERROR, false];

        yield 'skipped' => [DetectionStatus::SKIPPED, false];

        yield 'not covered' => [DetectionStatus::NOT_COVERED, false];

        yield 'ignored' => [DetectionStatus::IGNORED, false];
    }
}
