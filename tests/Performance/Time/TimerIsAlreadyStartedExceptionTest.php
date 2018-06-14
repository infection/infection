<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Performance\Time;

use Infection\Performance\Time\TimerIsAlreadyStartedException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TimerIsAlreadyStartedExceptionTest extends TestCase
{
    public function test_creation()
    {
        $exception = TimerIsAlreadyStartedException::create();

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(TimerIsAlreadyStartedException::class, $exception);
        $this->assertSame($exception->getMessage(), 'Timer can not be started again without stopping.');
    }
}
