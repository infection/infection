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

namespace Infection\Tests\Metrics;

use Infection\Metrics\MaxTimeoutsChecker;
use Infection\Metrics\MaxTimeoutCountReached;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MaxTimeoutsChecker::class)]
final class MaxTimeoutsCheckerTest extends TestCase
{
    public function test_it_does_nothing_when_max_timeouts_is_null(): void
    {
        $checker = new MaxTimeoutsChecker(null);

        $checker->checkTimeouts(100);

        $this->addToAssertionCount(1);
    }

    public function test_it_does_nothing_when_timed_out_count_is_below_limit(): void
    {
        $checker = new MaxTimeoutsChecker(10);

        $checker->checkTimeouts(5);

        $this->addToAssertionCount(1);
    }

    public function test_it_does_nothing_when_timed_out_count_equals_limit(): void
    {
        $checker = new MaxTimeoutsChecker(10);

        $checker->checkTimeouts(10);

        $this->addToAssertionCount(1);
    }

    public function test_it_throws_when_timed_out_count_exceeds_limit(): void
    {
        $checker = new MaxTimeoutsChecker(5);

        try {
            $checker->checkTimeouts(10);

            $this->fail('Expected MaxTimeoutCountReached to be thrown');
        } catch (MaxTimeoutCountReached $exception) {
            $this->assertSame(
                'The maximum allowed timeouts is 5, but 10 timed out. Reduce timeouts or increase the limit!',
                $exception->getMessage(),
            );
        }
    }

    public function test_it_throws_when_timed_out_count_exceeds_zero_limit(): void
    {
        $checker = new MaxTimeoutsChecker(0);

        try {
            $checker->checkTimeouts(1);

            $this->fail('Expected MaxTimeoutCountReached to be thrown');
        } catch (MaxTimeoutCountReached $exception) {
            $this->assertSame(
                'The maximum allowed timeouts is 0, but 1 timed out. Reduce timeouts or increase the limit!',
                $exception->getMessage(),
            );
        }
    }
}
