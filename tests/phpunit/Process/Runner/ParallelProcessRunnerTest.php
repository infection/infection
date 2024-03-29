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

namespace Infection\Tests\Process\Runner;

use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Process\Runner\ParallelProcessRunner;
use Infection\Process\Runner\ProcessBearer;
use Infection\Tests\Fixtures\Event\DummyEvent;
use Infection\Tests\Fixtures\Event\EventDispatcherCollector;
use Infection\Tests\Fixtures\Process\DummyProcessBearer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

#[CoversClass(ParallelProcessRunner::class)]
final class ParallelProcessRunnerTest extends TestCase
{
    public function test_it_does_nothing_when_no_process_is_given(): void
    {
        $runner = new ParallelProcessRunner(4, 0);

        $runner->run([]);

        $this->addToAssertionCount(1);
    }

    public function test_it_starts_the_given_processes(): void
    {
        $eventDispatcher = new EventDispatcherCollector();

        $threadsCount = 4;

        $processes = (function () use ($eventDispatcher, $threadsCount): iterable {
            for ($i = 0; $i < 10; ++$i) {
                yield $this->createProcessBearer($eventDispatcher, ($i % $threadsCount) + 1);
            }
        })();

        $runner = new ParallelProcessRunner($threadsCount, 0);

        $this->assertDummyEventCounts(0, $eventDispatcher->getEvents());

        $runner->run($processes);

        $this->assertDummyEventCounts(10, $eventDispatcher->getEvents());
    }

    public function test_it_checks_if_the_executed_processes_time_out(): void
    {
        $eventDispatcher = new EventDispatcherCollector();

        $processes = (function () use ($eventDispatcher): iterable {
            for ($i = 0; $i < 10; ++$i) {
                yield $this->createTimeOutProcessBearer($eventDispatcher);
            }
        })();

        $runner = new ParallelProcessRunner(4, 0);

        $this->assertDummyEventCounts(0, $eventDispatcher->getEvents());

        $runner->run($processes);

        $this->assertDummyEventCounts(10, $eventDispatcher->getEvents());
    }

    #[DataProvider('threadCountProvider')]
    public function test_it_handles_all_kids_of_processes_with_infinite_threads(int $threadCount): void
    {
        $this->runWithAllKindsOfProcesses($threadCount);
    }

    public static function threadCountProvider(): iterable
    {
        yield 'no threads' => [0];

        yield 'one thread' => [1];

        yield 'invalid thread' => [-1];

        yield 'nominal' => [4];

        yield 'thread count more than processes' => [20];
    }

    private function runWithAllKindsOfProcesses(int $threadCount): void
    {
        $eventDispatcher = new EventDispatcherCollector();

        $processes = (function () use ($eventDispatcher, $threadCount): iterable {
            for ($i = 0; $i < 5; ++$i) {
                $threadIndex = $threadCount === 0 ? 1 : ($i * 2 % $threadCount) + 1;

                yield $this->createProcessBearer($eventDispatcher, $threadIndex);

                yield $this->createTimeOutProcessBearer($eventDispatcher);
            }
        })();

        $this->assertDummyEventCounts(0, $eventDispatcher->getEvents());

        $runner = new ParallelProcessRunner($threadCount, 0);

        $runner->run($processes);

        $this->assertDummyEventCounts(10, $eventDispatcher->getEvents());
    }

    private function createProcessBearer(EventDispatcher $eventDispatcher, int $threadIndex): ProcessBearer
    {
        $processMock = $this->createMock(Process::class);
        $processMock
            ->expects($this->once())
            ->method('start')
            ->with(null, [
                'INFECTION' => '1',
                'TEST_TOKEN' => $threadIndex,
            ])
        ;
        $processMock
            ->expects($this->once())
            ->method('checkTimeout')
        ;
        $processMock
            ->expects($this->once())
            ->method('isRunning')
            ->willReturn(false)
        ;

        return new DummyProcessBearer(
            $processMock,
            false,
            static function () use ($eventDispatcher): void {
                $eventDispatcher->dispatch(new DummyEvent());
            },
        );
    }

    private function createTimeOutProcessBearer(EventDispatcher $eventDispatcher): ProcessBearer
    {
        $processMock = $this->createMock(Process::class);
        $processMock
            ->expects($this->once())
            ->method('start')
        ;
        $processMock
            ->expects($this->once())
            ->method('checkTimeout')
            ->willThrowException(new ProcessTimedOutException($processMock, 1))
        ;
        $processMock
            ->expects($this->once())
            ->method('isRunning')
            ->willReturn(false)
        ;

        return new DummyProcessBearer(
            $processMock,
            true,
            static function () use ($eventDispatcher): void {
                $eventDispatcher->dispatch(new DummyEvent());
            },
        );
    }

    /**
     * @param object[] $events
     */
    private function assertDummyEventCounts(int $expectedCount, array $events): void
    {
        $this->assertCount($expectedCount, $events);

        foreach ($events as $event) {
            $this->assertInstanceOf(DummyEvent::class, $event);
        }
    }
}
