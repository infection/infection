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

use Closure;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasStarted;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisSubStepWasCompleted;
use Infection\Process\Runner\InitialStaticAnalysisRunner;
use Infection\StaticAnalysis\StaticAnalysisToolAdapter;
use Infection\TestFramework\Contracts\CompletedProcess;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\Tests\TestFramework\Contracts\CompletedProcessBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ExceptionInterface as ProcessException;

#[CoversClass(InitialStaticAnalysisRunner::class)]
final class InitialStaticAnalysisRunnerTest extends TestCase
{
    private ShellCommandRunner&MockObject $shellCommandRunner;

    private StaticAnalysisToolAdapter&MockObject $adapter;

    private EventDispatcher&MockObject $eventDispatcher;

    private InitialStaticAnalysisRunner $runner;

    protected function setUp(): void
    {
        $this->shellCommandRunner = $this->createMock(ShellCommandRunner::class);
        $this->adapter = $this->createMock(StaticAnalysisToolAdapter::class);
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->runner = new InitialStaticAnalysisRunner(
            $this->shellCommandRunner,
            $this->adapter,
            $this->eventDispatcher,
        );
    }

    /**
     * @throws ProcessException
     */
    public function test_it_runs_the_initial_static_analysis_and_dispatches_events(): void
    {
        $command = ['phpstan', 'analyse'];
        $expected = CompletedProcessBuilder::withMinimalTestData()
            ->withCommand($command)
            ->withStdout('analysis output')
            ->build();

        $this->adapter
            ->expects($this->once())
            ->method('getInitialRunCommandLine')
            ->willReturn($command);

        $this->shellCommandRunner
            ->expects($this->once())
            ->method('run')
            ->with(
                $command,
                $this->isInstanceOf(Closure::class),
                null,
                [],
                null,
                null,
            )
            ->willReturnCallback(
                static function (array $_command, Closure $callback) use ($expected): CompletedProcess {
                    $callback('out', 'analysis output');

                    return $expected;
                },
            );

        $expectedEvents = [
            new InitialStaticAnalysisRunWasStarted(),
            new InitialStaticAnalysisSubStepWasCompleted(),
            new InitialStaticAnalysisRunWasFinished('analysis output'),
        ];
        $eventIndex = 0;

        $this->eventDispatcher
            ->expects($this->exactly(3))
            ->method('dispatch')
            ->willReturnCallback(function (object $event) use ($expectedEvents, &$eventIndex): void {
                $this->assertEquals($expectedEvents[$eventIndex], $event);
                ++$eventIndex;
            });

        $actual = $this->runner->run();

        $this->assertEquals($expected, $actual);
    }
}
