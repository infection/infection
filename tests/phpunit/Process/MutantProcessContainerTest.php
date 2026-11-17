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
        $process = $this->createProcessWithoutResultRetrieval();
        $container = self::createContainer($process);

        $this->assertSame($process, $container->getCurrent());
        $this->assertFalse($container->hasNext());
    }

    #[DataProvider('detectionStatusProvider')]
    public function test_only_escaped_mutants_have_a_follow_up_process(DetectionStatus $status, bool $hasNext): void
    {
        $process = $this->createProcessWithStatus($status);
        $container = self::createContainer($process)
            ->append(function (): MutantProcess {
                $this->fail('Checking for a follow-up must not create its process.');
            })
        ;

        $this->assertSame($hasNext, $container->hasNext());
    }

    public function test_it_creates_each_process_lazily_once_and_advances_in_order(): void
    {
        $first = $this->createProcessWithStatus(DetectionStatus::ESCAPED);
        $second = $this->createProcessWithStatus(DetectionStatus::ESCAPED);
        $third = $this->createProcessWithStatus(DetectionStatus::KILLED_BY_STATIC_ANALYSIS);
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

    public function test_it_preserves_nested_follow_ups_without_another_outer_factory(): void
    {
        $first = $this->createProcessWithStatus(DetectionStatus::ESCAPED);
        $second = $this->createProcessWithoutResultRetrieval();
        $nested = self::createContainer($first, $second);

        $container = self::createContainer($nested);

        $expected = [$first, $second];
        $actual = self::collectProcesses($container);

        $this->assertSame($expected, $actual);
    }

    #[DataProvider('detectionStatusProvider')]
    public function test_it_completes_a_nested_container_before_considering_the_next_process(
        DetectionStatus $status,
        bool $hasNext,
    ): void {
        $first = $this->createProcessWithStatus(DetectionStatus::ESCAPED);
        $second = $this->createProcessWithStatus($status);
        $third = $this->createProcessWithStatus(DetectionStatus::ESCAPED);

        $nested = self::createContainer($first, $second);
        $container = self::createContainer($nested, $third);

        $expectedProcesses = $hasNext
            ? [$first, $second, $third]
            : [$first, $second];
        $expectedResult = $hasNext
            ? $third->getMutantExecutionResult()
            : $second->getMutantExecutionResult();
        $processes = self::collectProcesses($container);

        $this->assertSame($expectedProcesses, $processes);
        $this->assertSame($expectedResult, $container->getMutantExecutionResult());
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

    private function createProcessWithStatus(DetectionStatus $status): MutantProcess
    {
        $result = MutantExecutionResultBuilder::withMinimalTestData()
            ->withDetectionStatus($status)
            ->build()
        ;

        $process = $this->createStub(MutantProcess::class);
        $process
            ->method('getMutantExecutionResult')
            ->willReturn($result)
        ;

        return $process;
    }

    private function createProcessWithoutResultRetrieval(): MutantProcess
    {
        $process = $this->createMock(MutantProcess::class);
        $process
            ->expects($this->never())
            ->method('getMutantExecutionResult')
        ;

        return $process;
    }

    private static function createContainer(
        MutantProcess|MutantProcessContainer $process,
        MutantProcess|MutantProcessContainer ...$processes,
    ): MutantProcessContainer {
        $container = MutantProcessContainer::from(static fn (): MutantProcess|MutantProcessContainer => $process);

        foreach ($processes as $process) {
            $container->append(static fn (): MutantProcess|MutantProcessContainer => $process);
        }

        return $container;
    }

    /**
     * @return non-empty-list<MutantProcess>
     */
    private static function collectProcesses(MutantProcessContainer $container): array
    {
        $processes = [$container->getCurrent()];

        while ($container->hasNext()) {
            $processes[] = $container->createNext();
        }

        return $processes;
    }
}
