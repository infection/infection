<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Performance\Time;

use Infection\Performance\Time\TimerNotStartedException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TimerNotStartedExceptionTest extends TestCase
{
    public function test_creation()
    {
        $exception = TimerNotStartedException::create();

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(TimerNotStartedException::class, $exception);
        $this->assertSame($exception->getMessage(), 'Timer must be started before stopping.');
    }
}
