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

namespace Infection\Tests\TestFramework;

use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestCaseWasCompleted;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasStarted;
use Infection\Mutant\Mutant;
use Infection\Process\MutantProcessContainer;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\StaticAnalysisTestFramework;
use Infection\TestFramework\EventDispatchingStaticAnalysisTestFramework;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(EventDispatchingStaticAnalysisTestFramework::class)]
final class EventDispatchingStaticAnalysisTestFrameworkTest extends TestCase
{
    #[DataProvider('initialRunProvider')]
    public function test_it_dispatches_initial_run_events_and_forwards_progress(
        ?string $version,
        string $expectedVersion,
        bool $reportCallbacks,
    ): void {
        $events = [];
        $result = new InitialRunResults('static-analysis output', null);
        $decorated = $this->createMock(StaticAnalysisTestFramework::class);
        $decorated->method('getName')->willReturn('Framework');
        $versionExpectation = $decorated->method('getVersion');

        if ($version === null) {
            $versionExpectation->willThrowException(new InvalidArgumentException('Invalid version output.'));
        } else {
            $versionExpectation->willReturn($version);
        }

        $decorated
            ->expects($this->once())
            ->method('executeInitialRun')
            ->willReturnCallback(function (callable $onProgress, callable $onStart) use ($result, $expectedVersion, &$events): InitialRunResults {
                $onStart("'framework' '--config=some config.xml'");

                $expectedEvents = [new InitialTestSuiteWasStarted("'framework' '--config=some config.xml'", 'Framework', $expectedVersion)];

                $this->assertEquals($expectedEvents, $events);

                $onProgress();

                return $result;
            })
        ;

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher
            ->expects($this->exactly(3))
            ->method('dispatch')
            ->willReturnCallback(static function (object $event) use (&$events): void {
                $events[] = $event;
            })
        ;

        $progressCalls = 0;
        $onProgress = static function () use (&$progressCalls): void {
            ++$progressCalls;
        };
        $startedCommands = [];
        $onStart = static function (string $commandLine) use (&$startedCommands): void {
            $startedCommands[] = $commandLine;
        };
        $framework = new EventDispatchingStaticAnalysisTestFramework($decorated, $eventDispatcher);
        $actual = $framework->executeInitialRun(
            $reportCallbacks
                ? $onProgress
                : null,
            $reportCallbacks
                ? $onStart
                : null,
        );

        $this->assertSame($result, $actual);
        $this->assertSame(
            $reportCallbacks
                ? 1
                : 0,
            $progressCalls,
        );
        $expectedStartedCommands = $reportCallbacks
            ? ["'framework' '--config=some config.xml'"]
            : [];

        $this->assertSame($expectedStartedCommands, $startedCommands);
        $expectedEvents = [
            new InitialTestSuiteWasStarted("'framework' '--config=some config.xml'", 'Framework', $expectedVersion),
            new InitialTestCaseWasCompleted('Framework'),
            new InitialTestSuiteWasFinished('Framework', 'static-analysis output'),
        ];

        $this->assertEquals($expectedEvents, $events);
    }

    public static function initialRunProvider(): iterable
    {
        yield 'known version without callbacks' => ['1.2.3', '1.2.3', false];

        yield 'known version with callbacks' => ['1.2.3', '1.2.3', true];

        yield 'unknown version without callbacks' => [null, 'unknown', false];

        yield 'unknown version with callbacks' => [null, 'unknown', true];
    }

    public function test_it_preserves_initial_run_failures_without_dispatching_a_finish_event(): void
    {
        $failure = new RuntimeException('Initial run failed');
        $decorated = $this->createMock(StaticAnalysisTestFramework::class);
        $decorated->method('getName')->willReturn('Framework');
        $decorated->method('getVersion')->willReturn('1.2.3');
        $decorated
            ->expects($this->once())
            ->method('executeInitialRun')
            ->willReturnCallback(static function (callable $_onProgress, callable $onStart) use ($failure): never {
                $onStart('framework');

                throw $failure;
            })
        ;
        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher->expects($this->once())->method('dispatch')->with(new InitialTestSuiteWasStarted('framework', 'Framework', '1.2.3'));
        $framework = new EventDispatchingStaticAnalysisTestFramework($decorated, $eventDispatcher);

        $this->expectExceptionObject($failure);

        $framework->executeInitialRun();
    }

    public function test_it_preserves_failures_when_reading_the_framework_version(): void
    {
        $failure = new InvalidArgumentException('Invalid version output.');
        $decorated = $this->createMock(StaticAnalysisTestFramework::class);
        $decorated->expects($this->once())->method('getVersion')->willThrowException($failure);
        $dispatcher = $this->createMock(EventDispatcher::class);
        $dispatcher->expects($this->never())->method('dispatch');
        $framework = new EventDispatchingStaticAnalysisTestFramework($decorated, $dispatcher);

        $this->expectExceptionObject($failure);

        $framework->getVersion();
    }

    #[DataProvider('reportProvider')]
    public function test_it_delegates_framework_operations(bool $hasJUnitReport): void
    {
        $mutant = $this->createStub(Mutant::class);
        $result = $this->createStub(MutantProcessContainer::class);
        $decorated = $this->createMock(StaticAnalysisTestFramework::class);
        $decorated->expects($this->once())->method('getName')->willReturn('Framework');
        $decorated->expects($this->once())->method('getVersion')->willReturn('1.2.3');
        $decorated->expects($this->once())->method('checkRequirements');
        $decorated->expects($this->once())->method('hasJUnitReport')->willReturn($hasJUnitReport);
        $decorated->expects($this->once())->method('test')->with($mutant)->willReturn($result);
        $dispatcher = $this->createMock(EventDispatcher::class);
        $dispatcher->expects($this->never())->method('dispatch');
        $framework = new EventDispatchingStaticAnalysisTestFramework($decorated, $dispatcher);

        $this->assertSame('Framework', $framework->getName());
        $this->assertSame('1.2.3', $framework->getVersion());
        $this->assertSame($hasJUnitReport, $framework->hasJUnitReport());
        $this->assertSame($result, $framework->test($mutant));

        $framework->checkRequirements();
    }

    public static function reportProvider(): iterable
    {
        yield 'with JUnit report' => [true];

        yield 'without JUnit report' => [false];
    }
}
