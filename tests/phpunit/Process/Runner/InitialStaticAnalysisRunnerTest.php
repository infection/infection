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
use Infection\Event\InitialStaticAnalysisRunWasFinished;
use Infection\Event\InitialStaticAnalysisRunWasStarted;
use Infection\Event\InitialStaticAnalysisSubStepWasCompleted;
use Infection\Process\Factory\InitialStaticAnalysisProcessFactory;
use Infection\Process\Runner\InitialStaticAnalysisRunner;
use Infection\Tests\Fixtures\Event\EventDispatcherCollector;
use Infection\Tests\TestingUtility\Process\TestPhpExecutableFinder;
use const PHP_SAPI;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[Group('integration')]
#[CoversClass(InitialStaticAnalysisRunner::class)]
final class InitialStaticAnalysisRunnerTest extends TestCase
{
    private InitialStaticAnalysisProcessFactory&MockObject $processFactoryMock;

    private EventDispatcherCollector $eventDispatcher;

    private InitialStaticAnalysisRunner $runner;

    protected function setUp(): void
    {
        if (PHP_SAPI === 'phpdbg') {
            $this->markTestSkipped('The processes do not work the same way in PGPDBG');
        }

        $this->processFactoryMock = $this->createMock(InitialStaticAnalysisProcessFactory::class);

        $this->eventDispatcher = new EventDispatcherCollector();

        $this->runner = new InitialStaticAnalysisRunner($this->processFactoryMock, $this->eventDispatcher);
    }

    public function test_it_creates_a_process_execute_it_and_dispatch_events_accordingly(): void
    {
        $process = $this->createProcessForCode(<<<STR
            echo 'ping';
            echo 'pong';
            STR
        );

        $this->processFactoryMock
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

    private function createProcessForCode(string $code): Process
    {
        return new Process([
            TestPhpExecutableFinder::find(),
            '-r',
            $code,
        ]);
    }
}
