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

namespace Infection\Tests\Event\Subscriber;

use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\EventDispatcher\SyncEventDispatcher;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestCaseWasCompleted;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasStarted;
use Infection\Event\Subscriber\InitialTestsExecutionLoggerSubscriber;
use Infection\Logger\ArtefactCollection\InitialTestsExecution\InitialTestsExecutionLogger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(InitialTestsExecutionLoggerSubscriber::class)]
final class InitialTestsExecutionLoggerSubscriberTest extends TestCase
{
    private MockObject&InitialTestsExecutionLogger $loggerMock;

    private EventDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(InitialTestsExecutionLogger::class);

        $subscriber = new InitialTestsExecutionLoggerSubscriber(
            $this->loggerMock,
        );

        $this->dispatcher = new SyncEventDispatcher();
        $this->dispatcher->addSubscriber($subscriber);
    }

    public function test_it_reacts_on_initial_test_suite_was_started(): void
    {
        $this->loggerMock
            ->expects($this->once())
            ->method('start');

        $this->dispatcher->dispatch(
            new InitialTestSuiteWasStarted(),
        );
    }

    public function test_it_reacts_on_initial_test_case_was_completed(): void
    {
        $this->loggerMock
            ->expects($this->once())
            ->method('advance');

        $this->dispatcher->dispatch(
            new InitialTestCaseWasCompleted(),
        );
    }

    public function test_it_reacts_on_initial_test_suite_was_finished(): void
    {
        $this->loggerMock
            ->expects($this->once())
            ->method('finish')
            ->with($this->identicalTo('output'));

        $this->dispatcher->dispatch(
            new InitialTestSuiteWasFinished(
                'output',
            ),
        );
    }
}
