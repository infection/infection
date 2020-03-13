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

namespace Infection\Tests\Resource\Time;

use Infection\Resource\Time\Stopwatch;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

// Cannot import this one as it would remove the ability to mock it
// use function usleep()

/**
 * @group time-sensitive
 */
final class StopwatchTest extends TestCase
{
    /**
     * @var Stopwatch
     */
    private $stopwatch;

    protected function setUp(): void
    {
        $this->stopwatch = new Stopwatch();
    }

    /**
     * @dataProvider timeProvider
     */
    public function test_it_returns_the_time_took_on_stop(int $sleepTime, float $expectedTime): void
    {
        $this->stopwatch->start();

        usleep($sleepTime);

        $actualTimeInSeconds = $this->stopwatch->stop();

        $this->assertSame($expectedTime, $actualTimeInSeconds);
    }

    public function test_it_cannot_be_started_twice(): void
    {
        $this->stopwatch->start();

        try {
            $this->stopwatch->start();

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Timer can not be started again without stopping.',
                $exception->getMessage()
            );
        }
    }

    public function test_it_cannot_stop_if_was_not_started(): void
    {
        try {
            $this->stopwatch->stop();

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Timer must be started before stopping.',
                $exception->getMessage()
            );
        }
    }

    public function timeProvider(): iterable
    {
        yield 'no time' => [0, 0.];

        yield 'nominal' => [10000000, 10.0];
    }
}
