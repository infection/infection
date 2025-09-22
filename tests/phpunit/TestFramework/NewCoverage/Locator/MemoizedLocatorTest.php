<?php

namespace Infection\Tests\TestFramework\NewCoverage\Locator;

use Infection\TestFramework\NewCoverage\Locator\MemoizedLocator;
use Infection\TestFramework\NewCoverage\Locator\NoReportFound;
use Infection\TestFramework\NewCoverage\Locator\ReportLocator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MemoizedLocator::class)]
final class MemoizedLocatorTest extends TestCase
{
    public function test_it_delegates_to_decorated_locator_on_first_call_and_delegate_the_cached_result_on_subsequent_calls(): void
    {
        $expected = '/path/to/report.xml';

        $decoratedLocator = $this->createMock(ReportLocator::class);
        $decoratedLocator
            ->expects(self::once())
            ->method('locate')
            ->willReturn($expected);

        $memoizedLocator = new MemoizedLocator($decoratedLocator);

        self::assertSame($expected, $memoizedLocator->locate());
        self::assertSame($expected, $memoizedLocator->locate());
    }

    public function test_it_propagates_exceptions_from_decorated_locator(): void
    {
        $exception = new NoReportFound('Report not found');

        $decoratedLocator = $this->createMock(ReportLocator::class);
        $decoratedLocator
            ->expects(self::once())
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
            ->expects(self::exactly(2))
            ->method('locate')
            ->willReturnOnConsecutiveCalls(
                $this->throwException($thrownException),
                $expected,
            );

        $memoizedLocator = new MemoizedLocator($decoratedLocator);

        try {
            $memoizedLocator->locate();
            self::fail('Expected exception to be thrown');
        } catch (NoReportFound $caughtException) {
            self::assertSame($thrownException, $caughtException);
        }

        self::assertSame($expected, $memoizedLocator->locate());
        self::assertSame($expected, $memoizedLocator->locate());
    }
}
