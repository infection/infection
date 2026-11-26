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
use function end;
use Infection\AbstractTestFramework\InvalidVersion;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestCaseWasCompleted;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasStarted;
use Infection\Process\Factory\InitialTestsRunProcessFactory;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\TestFramework\Contracts\TestFrameworkLogger;
use Infection\TestFramework\Logger\EventDispatchingTestFrameworkLogger;
use Infection\Tests\Fixtures\Event\EventDispatcherCollector;
use Infection\Tests\TestingUtility\Process\TestPhpExecutableFinder;
use const PHP_SAPI;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

#[CoversClass(InitialTestsRunner::class)]
final class InitialTestsRunnerTest extends TestCase
{
    private MockObject&InitialTestsRunProcessFactory $processFactoryMock;

    private EventDispatcherCollector $eventDispatcher;

    private InitialTestsRunner $runner;

    private EventDispatchingTestFrameworkLogger $testFrameworkLogger;

    private TestFrameworkAdapter&Stub $testFrameworkAdapter;

    protected function setUp(): void
    {
        if (PHP_SAPI === 'phpdbg') {
            $this->markTestSkipped('The processes do not work the same way in PGPDBG');
        }

        $this->processFactoryMock = $this->createMock(InitialTestsRunProcessFactory::class);

        $this->eventDispatcher = new EventDispatcherCollector();

        $this->testFrameworkLogger = new EventDispatchingTestFrameworkLogger($this->eventDispatcher);
        $this->runner = new InitialTestsRunner($this->processFactoryMock);
        $this->testFrameworkAdapter = $this->createStub(TestFrameworkAdapter::class);
    }

    #[DataProvider('versionProvider')]
    public function test_it_creates_a_process_execute_it_and_dispatch_events_accordingly(?string $version, string $expectedVersion): void
    {
        $this->testFrameworkAdapter
            ->method('getName')
            ->willReturn('PHPUnit')
        ;
        $versionExpectation = $this->testFrameworkAdapter->method('getVersion');

        if ($version === null) {
            $versionExpectation->willThrowException(new InvalidVersion('Invalid version output.'));
        } else {
            $versionExpectation->willReturn($version);
        }

        $testFrameworkExtraOptions = '--stop-on-failure';
        $phpExtraOptions = ['-d memory_limit=-1'];
        $skipCoverage = false;

        $process = $this->createProcessForCode(<<<STR
            echo 'ping';
            echo 'pong';
            STR
        );

        $this->processFactoryMock
            ->method('createProcess')
            ->with($this->testFrameworkAdapter, $testFrameworkExtraOptions, $phpExtraOptions, $skipCoverage)
            ->willReturn($process)
        ;

        $this->runner->run($this->testFrameworkAdapter, $testFrameworkExtraOptions, $phpExtraOptions, $skipCoverage, $this->testFrameworkLogger);

        $this->assertSame(
            [
                InitialTestSuiteWasStarted::class,
                InitialTestCaseWasCompleted::class,
                InitialTestSuiteWasFinished::class,
            ],
            array_values(array_unique(array_map(get_class(...), $this->eventDispatcher->getEvents()))),
        );
        $this->assertEquals(
            new InitialTestSuiteWasStarted($process->getCommandLine(), 'PHPUnit', $expectedVersion),
            $this->eventDispatcher->getEvents()[0],
        );

        $events = $this->eventDispatcher->getEvents();

        $this->assertContainsEquals(new InitialTestCaseWasCompleted('PHPUnit'), $events);
        $this->assertEquals(
            new InitialTestSuiteWasFinished('PHPUnit', 'pingpong'),
            end($events),
        );
    }

    #[DataProvider('exitCodeProvider')]
    public function test_it_continues_after_stderr_output_and_preserves_the_exit_code(int $exitCode): void
    {
        $this->testFrameworkAdapter->method('getName')->willReturn('PHPUnit');
        $testFrameworkExtraOptions = '--stop-on-failure';
        $phpExtraOptions = ['-d memory_limit=-1'];
        $skipCoverage = false;
        $input = new InputStream();

        // Wait for the progress notification before continuing after stderr output.
        $process = $this->createProcessForCode(<<<PHP
            fwrite(STDERR, 'diagnostic');
            fgets(STDIN);
            echo 'continued';
            exit($exitCode);
            PHP
        );
        $process->setInput($input);
        $process->setTimeout(2.0);

        $this->processFactoryMock
            ->method('createProcess')
            ->with($this->testFrameworkAdapter, $testFrameworkExtraOptions, $phpExtraOptions, $skipCoverage)
            ->willReturn($process)
        ;

        $logger = $this->createMock(TestFrameworkLogger::class);
        $logger
            ->expects($this->atLeastOnce())
            ->method('initialRunProgressed')
            ->with('PHPUnit')
            ->willReturnCallback(static function () use ($input): void {
                if (!$input->isClosed()) {
                    $input->write("continue\n");
                    $input->close();
                }
            })
        ;
        $logger
            ->expects($this->once())
            ->method('initialRunFinished')
            ->with('PHPUnit', 'continued')
        ;

        $actual = $this->runner->run(
            $this->testFrameworkAdapter,
            $testFrameworkExtraOptions,
            $phpExtraOptions,
            $skipCoverage,
            $logger,
        );

        $this->assertSame($process, $actual);
        $this->assertSame($exitCode, $actual->getExitCode());
        $this->assertSame('continued', $actual->getOutput());
        $this->assertSame('diagnostic', $actual->getErrorOutput());
    }

    public static function exitCodeProvider(): iterable
    {
        yield 'successful command with stderr output' => [0];

        yield 'failed command with stderr output' => [1];
    }

    public static function versionProvider(): iterable
    {
        yield 'known version' => ['6.5.4', '6.5.4'];

        yield 'unknown version' => [null, 'unknown'];
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
