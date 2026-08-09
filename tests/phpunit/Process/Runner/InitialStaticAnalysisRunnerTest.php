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
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasStarted;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisSubStepWasCompleted;
use Infection\Process\Factory\InitialStaticAnalysisProcessFactory;
use Infection\Process\Runner\InitialStaticAnalysisRunFailed;
use Infection\Process\Runner\InitialStaticAnalysisRunner;
use Infection\StaticAnalysis\StaticAnalysisToolAdapter;
use Infection\Tests\Fixtures\Event\EventDispatcherCollector;
use Infection\Tests\TestingUtility\Process\TestPhpExecutableFinder;
use const PHP_SAPI;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[CoversClass(InitialStaticAnalysisRunner::class)]
final class InitialStaticAnalysisRunnerTest extends TestCase
{
    private InitialStaticAnalysisProcessFactory&Stub $processFactoryStub;

    private EventDispatcherCollector $eventDispatcher;

    private StaticAnalysisToolAdapter&Stub $staticAnalysisToolAdapter;

    private InitialStaticAnalysisRunner $runner;

    protected function setUp(): void
    {
        if (PHP_SAPI === 'phpdbg') {
            $this->markTestSkipped('The processes do not work the same way in PGPDBG');
        }

        $this->processFactoryStub = $this->createStub(InitialStaticAnalysisProcessFactory::class);

        $this->eventDispatcher = new EventDispatcherCollector();

        $this->staticAnalysisToolAdapter = $this->createStub(StaticAnalysisToolAdapter::class);

        $this->runner = new InitialStaticAnalysisRunner(
            $this->processFactoryStub,
            $this->eventDispatcher,
            $this->staticAnalysisToolAdapter,
        );
    }

    public function test_it_creates_a_process_execute_it_and_dispatch_events_accordingly(): void
    {
        $process = $this->createProcessForCode(<<<STR
            echo 'ping';
            echo 'pong';
            STR
        );

        $this->processFactoryStub
            ->method('createProcess')
            ->willReturn($process)
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
        $process = $this->createProcessForCode('exit(3);');

        $this->processFactoryStub
            ->method('createProcess')
            ->willReturn($process)
        ;

        $this->staticAnalysisToolAdapter
            ->method('getName')
            ->willReturn('phpstan')
        ;

        $this->expectException(InitialStaticAnalysisRunFailed::class);
        $this->expectExceptionMessageMatches('/phpstan reported an exit code of 3\\./');

        $this->runner->run();
    }

    private function createProcessForCode(string $code): Process
    {
        return new Process([
            TestPhpExecutableFinder::find(),
            '-r',
            $code,
        ]);
    }
}
