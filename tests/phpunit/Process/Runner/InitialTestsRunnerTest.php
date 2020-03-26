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
use Infection\Event\InitialTestCaseWasCompleted;
use Infection\Event\InitialTestSuiteWasFinished;
use Infection\Event\InitialTestSuiteWasStarted;
use Infection\Process\Factory\InitialTestsRunProcessFactory;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Tests\Fixtures\Event\EventDispatcherCollector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @group integration
 */
final class InitialTestsRunnerTest extends TestCase
{
    /**
     * @var string
     */
    private static $phpBin;

    /**
     * @var InitialTestsRunProcessFactory|MockObject
     */
    private $processFactoryMock;

    /**
     * @var EventDispatcherCollector
     */
    private $eventDispatcher;

    /**
     * @var InitialTestsRunner
     */
    private $runner;

    public static function setUpBeforeClass(): void
    {
        self::$phpBin = (new PhpExecutableFinder())->find();
    }

    protected function setUp(): void
    {
        $this->processFactoryMock = $this->createMock(InitialTestsRunProcessFactory::class);

        $this->eventDispatcher = new EventDispatcherCollector();

        $this->runner = new InitialTestsRunner($this->processFactoryMock, $this->eventDispatcher);
    }

    public function test_it_creates_a_process_execute_it_and_dispatch_events_accordingly(): void
    {
        $testFrameworkExtraOptions = '--stop-on-failure';
        $phpExtraOptions = ['-d memory_limit=-1'];
        $skipCoverage = false;

        $process = $this->createProcessForCode(<<<STR
echo 'ping';
sleep(1);
echo 'pong';
STR
        );

        $this->processFactoryMock
            ->method('createProcess')
            ->with($testFrameworkExtraOptions, $phpExtraOptions, $skipCoverage)
            ->willReturn($process)
        ;

        $this->runner->run($testFrameworkExtraOptions, $phpExtraOptions, $skipCoverage);

        $this->assertSame(
            [
                InitialTestSuiteWasStarted::class,
                InitialTestCaseWasCompleted::class,
                InitialTestCaseWasCompleted::class,
                InitialTestSuiteWasFinished::class,
            ],
            array_map('get_class', $this->eventDispatcher->getEvents())
        );
    }

    public function test_it_stops_the_process_execution_on_the_first_error(): void
    {
        $testFrameworkExtraOptions = '--stop-on-failure';
        $phpExtraOptions = ['-d memory_limit=-1'];
        $skipCoverage = false;

        $input = new InputStream();

        $process = $this->createProcessForCode(<<<STR
fwrite(STDOUT, 123);
fwrite(STDERR, 321);
fwrite(STDOUT, 123);
fwrite(STDERR, 321);
STR
        );
        $process->setInput($input);

        $this->processFactoryMock
            ->method('createProcess')
            ->with($testFrameworkExtraOptions, $phpExtraOptions, $skipCoverage)
            ->willReturn($process)
        ;

        $this->runner->run($testFrameworkExtraOptions, $phpExtraOptions, $skipCoverage);

        $this->assertSame(
            [
                InitialTestSuiteWasStarted::class,
                InitialTestCaseWasCompleted::class,
                InitialTestCaseWasCompleted::class,
                InitialTestSuiteWasFinished::class,
            ],
            array_map('get_class', $this->eventDispatcher->getEvents())
        );
    }

    private function createProcessForCode(string $code): Process
    {
        return new Process([self::$phpBin, '-r', $code]);
    }
}
