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

namespace Infection\Tests\TestFramework\Coverage\Locator;

use Infection\TestFramework\Coverage\Locator\MemoizedLocator;
use Infection\TestFramework\Coverage\Locator\ReportLocator;
use Infection\TestFramework\Coverage\Locator\Throwable\NoReportFound;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MemoizedLocator::class)]
final class MemoizedLocatorTest extends TestCase
{
    public function test_it_delegates_to_decorated_locator_on_first_call_and_delegate_the_cached_result_on_subsequent_calls(): void
    {
        $expectedLocation = '/path/to/report.xml';
        $expectedDefaultLocation = '/path/to/default-report.xml';

        $decoratedLocator = $this->createMock(ReportLocator::class);
        $decoratedLocator
            ->expects($this->once())
            ->method('locate')
            ->willReturn($expectedLocation);
        $decoratedLocator
            ->expects($this->once())
            ->method('getDefaultLocation')
            ->willReturn($expectedDefaultLocation);

        $memoizedLocator = new MemoizedLocator($decoratedLocator);

        $this->assertSame($expectedLocation, $memoizedLocator->locate());
        $this->assertSame($expectedLocation, $memoizedLocator->locate());

        $this->assertSame($expectedDefaultLocation, $memoizedLocator->getDefaultLocation());
        $this->assertSame($expectedDefaultLocation, $memoizedLocator->getDefaultLocation());
    }

    public function test_it_propagates_exceptions_from_decorated_locator(): void
    {
        $exception = new NoReportFound('Report not found');

        $decoratedLocator = $this->createMock(ReportLocator::class);
        $decoratedLocator
            ->expects($this->once())
            ->method('locate')
            ->willThrowException($exception);

        $memoizedLocator = new MemoizedLocator($decoratedLocator);

        $this->expectExceptionObject($exception);

        $memoizedLocator->locate();
    }

    public function test_it_does_not_cache_exceptions_and_retries_on_subsequent_calls(): void
    {
        $thrownException = new NoReportFound('Report not found');
        $expected = '/path/to/report.xml';

        $decoratedLocator = $this->createMock(ReportLocator::class);
        $decoratedLocator
            ->expects($this->exactly(2))
            ->method('locate')
            ->willReturnOnConsecutiveCalls(
                $this->throwException($thrownException),
                $expected,
            );

        $memoizedLocator = new MemoizedLocator($decoratedLocator);

        try {
            $memoizedLocator->locate();
            $this->fail('Expected exception to be thrown');
        } catch (NoReportFound $caughtException) {
            $this->assertSame($thrownException, $caughtException);
        }

        $this->assertSame($expected, $memoizedLocator->locate());
        $this->assertSame($expected, $memoizedLocator->locate());
    }
}
