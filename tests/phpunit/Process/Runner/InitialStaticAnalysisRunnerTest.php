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

use function array_map;
use function array_unique;
use function array_values;
use Closure;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasStarted;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisSubStepWasCompleted;
use Infection\Process\Runner\InitialStaticAnalysisRunFailed;
use Infection\Process\Runner\InitialStaticAnalysisRunner;
use Infection\StaticAnalysis\StaticAnalysisToolAdapter;
use Infection\TestFramework\Contracts\CompletedProcess;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\Tests\Fixtures\Event\EventDispatcherCollector;
use Infection\Tests\TestFramework\Contracts\CompletedProcessBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(InitialStaticAnalysisRunner::class)]
final class InitialStaticAnalysisRunnerTest extends TestCase
{
    private ShellCommandRunner&MockObject $shellCommandRunner;

    private EventDispatcherCollector $eventDispatcher;

    private StaticAnalysisToolAdapter&MockObject $staticAnalysisToolAdapter;

    private InitialStaticAnalysisRunner $runner;

    protected function setUp(): void
    {
        $this->shellCommandRunner = $this->createMock(ShellCommandRunner::class);

        $this->eventDispatcher = new EventDispatcherCollector();

        $this->staticAnalysisToolAdapter = $this->createMock(StaticAnalysisToolAdapter::class);

        $this->runner = new InitialStaticAnalysisRunner(
            $this->shellCommandRunner,
            $this->eventDispatcher,
            $this->staticAnalysisToolAdapter,
        );
    }

    public function test_it_creates_a_process_execute_it_and_dispatch_events_accordingly(): void
    {
        $command = ['phpstan', 'analyse'];

        $this->staticAnalysisToolAdapter
            ->expects($this->once())
            ->method('getInitialRunCommandLine')
            ->willReturn($command)
        ;

        $this->shellCommandRunner
            ->expects($this->once())
            ->method('run')
            ->with($command, $this->isInstanceOf(Closure::class), null, [], null, null)
            ->willReturnCallback(static function (array $_command, Closure $callback): CompletedProcess {
                $callback();

                return CompletedProcessBuilder::withMinimalTestData()
                    ->withCommand(['phpstan', 'analyse'])
                    ->withStdout('pingpong')
                    ->build()
                ;
            })
        ;

        $this->runner->run();

        $this->assertSame(
            [
                InitialStaticAnalysisRunWasStarted::class,
                InitialStaticAnalysisSubStepWasCompleted::class,
                InitialStaticAnalysisRunWasFinished::class,
            ],
            array_values(array_unique(array_map(get_class(...), $this->eventDispatcher->getEvents()))),
        );
    }

    public function test_it_throws_when_the_static_analysis_process_fails(): void
    {
        $command = ['phpstan', 'analyse'];

        $this->staticAnalysisToolAdapter
            ->expects($this->once())
            ->method('getInitialRunCommandLine')
            ->willReturn($command)
        ;
        $this->staticAnalysisToolAdapter
            ->expects($this->once())
            ->method('getName')
            ->willReturn('phpstan')
        ;
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
            ->willReturn(
                CompletedProcessBuilder::withMinimalTestData()
                    ->withCommand($command)
                    ->withExitCode(3)
                    ->build(),
            )
        ;

        $this->expectException(InitialStaticAnalysisRunFailed::class);
        $this->expectExceptionMessageMatches('/phpstan reported an exit code of 3\\./');

        $this->runner->run();
    }
}
