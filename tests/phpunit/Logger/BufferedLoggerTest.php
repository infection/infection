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

namespace Infection\Tests\Logger;

use Infection\Logger\BufferedLogger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Stringable;

#[CoversClass(BufferedLogger::class)]
final class BufferedLoggerTest extends TestCase
{
    public function test_it_buffers_records_until_flushed_and_then_clears_them(): void
    {
        $decoratedLogger = new DummyLogger();
        $logger = new BufferedLogger($decoratedLogger);

        $this->assertTrue($logger->isEmpty());

        $logger->warning(
            'First message with {name}.',
            ['name' => 'context'],
        );
        $logger->notice('Second message.');

        $expected = [
            [LogLevel::WARNING, 'First message with {name}.', ['name' => 'context']],
            [LogLevel::NOTICE, 'Second message.', []],
        ];

        $this->assertFalse($logger->isEmpty());

        $recordsBeforeFlush = $decoratedLogger->getLogs();

        $this->assertSame([], $recordsBeforeFlush);

        $logger->flush();

        $recordsAfterFlush = $decoratedLogger->getLogs();

        $this->assertSame($expected, $recordsAfterFlush);
        $this->assertTrue($logger->isEmpty());

        $logger->flush();

        $recordsAfterSecondFlush = $decoratedLogger->getLogs();

        $this->assertSame($expected, $recordsAfterSecondFlush);

        $logger->error('Third message.');
        $logger->flush();

        $this->assertSame(
            [
                ...$expected,
                [LogLevel::ERROR, 'Third message.', []],
            ],
            $decoratedLogger->getLogs(),
        );
    }

    public function test_it_does_not_evaluate_stringable_record_values(): void
    {
        $message = $this->createStringableExpectingNoToStringCalls();
        $context = ['name' => $this->createStringableExpectingNoToStringCalls()];
        $decoratedLogger = $this->createMock(LoggerInterface::class);
        $logger = new BufferedLogger($decoratedLogger);

        $decoratedLogger
            ->expects($this->once())
            ->method('log')
            ->with(
                LogLevel::NOTICE,
                $this->identicalTo($message),
                $this->identicalTo($context),
            )
        ;

        $logger->notice($message, $context);
        $logger->flush();
    }

    private function createStringableExpectingNoToStringCalls(): Stringable
    {
        $stringable = $this->createMock(Stringable::class);
        $stringable
            ->expects($this->never())
            ->method('__toString')
        ;

        return $stringable;
    }
}
