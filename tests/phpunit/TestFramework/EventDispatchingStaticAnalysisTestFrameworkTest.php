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
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasStarted;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisSubStepWasCompleted;
use Infection\TestFramework\Contracts\InitialTestsResult;
use Infection\TestFramework\Contracts\StaticAnalysisTestFramework;
use Infection\TestFramework\EventDispatchingStaticAnalysisTestFramework;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventDispatchingStaticAnalysisTestFramework::class)]
final class EventDispatchingStaticAnalysisTestFrameworkTest extends TestCase
{
    public function test_it_dispatches_initial_run_events_and_forwards_progress(): void
    {
        $result = new InitialTestsResult('static-analysis output');
        $decorated = $this->createMock(StaticAnalysisTestFramework::class);
        $decorated
            ->expects($this->once())
            ->method('executeInitialRun')
            ->willReturnCallback(static function (callable $onProgress) use ($result): InitialTestsResult {
                $onProgress();

                return $result;
            });

        $events = [];
        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher
            ->expects($this->exactly(3))
            ->method('dispatch')
            ->willReturnCallback(static function (object $event) use (&$events): void {
                $events[] = $event;
            });

        $progressCalls = 0;
        $actual = (new EventDispatchingStaticAnalysisTestFramework($decorated, $eventDispatcher))
            ->executeInitialRun(static function () use (&$progressCalls): void {
                ++$progressCalls;
            });

        $this->assertSame($result, $actual);
        $this->assertSame(1, $progressCalls);
        $this->assertInstanceOf(InitialStaticAnalysisRunWasStarted::class, $events[0]);
        $this->assertInstanceOf(InitialStaticAnalysisSubStepWasCompleted::class, $events[1]);
        $this->assertInstanceOf(InitialStaticAnalysisRunWasFinished::class, $events[2]);
        $this->assertSame('static-analysis output', $events[2]->outputText);
    }
}
