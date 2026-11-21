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
use Infection\AbstractTestFramework\InvalidVersion;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestCaseWasCompleted;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasStarted;
use Infection\Process\Runner\InitialStaticAnalysisRunner;
use Infection\TestFramework\Common\Throwable\InitialTestsFailed;
use Infection\TestFramework\Contracts\CompletedProcess;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Logger\EventDispatchingTestFrameworkLogger;
use Infection\Tests\Fixtures\Event\EventDispatcherCollector;
use Infection\Tests\TestFramework\Contracts\CompletedProcessBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(InitialStaticAnalysisRunner::class)]
final class InitialStaticAnalysisRunnerTest extends TestCase
{
    private ShellCommandRunner&MockObject $shellCommandRunner;

    private EventDispatcherCollector $eventDispatcher;

    private TestFramework&MockObject $testFramework;

    private InitialStaticAnalysisRunner $runner;

    protected function setUp(): void
    {
        $this->shellCommandRunner = $this->createMock(ShellCommandRunner::class);

        $this->eventDispatcher = new EventDispatcherCollector();

        $this->testFramework = $this->createMock(TestFramework::class);

        $this->runner = new InitialStaticAnalysisRunner(
            $this->shellCommandRunner,
            new EventDispatchingTestFrameworkLogger($this->eventDispatcher),
        );
    }

    #[DataProvider('versionProvider')]
    public function test_it_creates_a_process_execute_it_and_dispatch_events_accordingly(?string $version, string $expectedVersion): void
    {
        $this->testFramework
            ->expects($this->once())
            ->method('getName')
            ->willReturn('PHPStan')
        ;
        $versionExpectation = $this->testFramework->method('getVersion');

        if ($version === null) {
            $versionExpectation->willThrowException(new InvalidVersion('Invalid version output.'));
        } else {
            $versionExpectation->willReturn($version);
        }

        $command = ['phpstan', 'analyse', '--configuration=some config.neon'];
        $executedCommand = ['/usr/bin/php', 'phpstan', 'analyse', '--configuration=some config.neon'];
        $commandLine = "'/usr/bin/php' 'phpstan' 'analyse' '--configuration=some config.neon'";

        $this->shellCommandRunner
            ->expects($this->once())
            ->method('run')
            ->with($command, $this->isInstanceOf(Closure::class), null, [], null, null)
            ->willReturnCallback(function (array $_command, Closure $callback) use ($executedCommand, $expectedVersion): CompletedProcess {
                $this->assertEquals(
                    [new InitialTestSuiteWasStarted('PHPStan', $expectedVersion)],
                    $this->eventDispatcher->getEvents(),
                );

                $callback();

                return CompletedProcessBuilder::withMinimalTestData()
                    ->withCommand($executedCommand)
                    ->withStdout('pingpong')
                    ->build()
                ;
            })
        ;

        $result = $this->runner->run($this->testFramework, $command);

        $this->assertEquals(new InitialRunResults('pingpong', null), $result);

        $this->assertEquals(
            [
                new InitialTestSuiteWasStarted('PHPStan', $expectedVersion),
                new InitialTestCaseWasCompleted('PHPStan'),
                new InitialTestSuiteWasFinished('PHPStan', $commandLine, 'pingpong'),
            ],
            $this->eventDispatcher->getEvents(),
        );
    }

    public function test_it_throws_when_the_static_analysis_process_fails(): void
    {
        $command = ['phpstan', 'analyse'];

        $this->testFramework
            ->expects($this->exactly(2))
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

        $this->expectException(InitialTestsFailed::class);
        $this->expectExceptionMessage(
            <<<'MESSAGE'
                Project static analysis must be in a passing state before running Infection.

                phpstan reported an exit code of 3.
                Refer to the phpstan's output below:
                MESSAGE,
        );

        $this->runner->run($this->testFramework, $command);
    }

    public static function versionProvider(): iterable
    {
        yield 'known version' => ['2.1.0', '2.1.0'];

        yield 'unknown version' => [null, 'unknown'];
    }
}
