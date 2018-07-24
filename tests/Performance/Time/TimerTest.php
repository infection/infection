<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Performance\Time;

use Infection\Performance\Time\Timer;
use Infection\Performance\Time\TimerIsAlreadyStartedException;
use Infection\Performance\Time\TimerNotStartedException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TimerTest extends TestCase
{
    /**
     * @var Timer
     */
    private $timer;

    protected function setUp()
    {
        $this->timer = new Timer();
    }

    public function test_it_returns_return_seconds_on_stop()
    {
        $this->timer->start();
        $timeInSeconds = $this->timer->stop();

        $this->assertInternalType('float', $timeInSeconds);
        $this->assertGreaterThan(0, $timeInSeconds);
    }

    public function test_it_throws_an_exception_when_started_twice_without_stopping()
    {
        $this->expectException(TimerIsAlreadyStartedException::class);

        $this->timer->start();
        $this->timer->start();
    }

    public function test_it_throws_an_exception_when_stopped_without_starting()
    {
        $this->expectException(TimerNotStartedException::class);

        $this->timer->stop();
    }
}
