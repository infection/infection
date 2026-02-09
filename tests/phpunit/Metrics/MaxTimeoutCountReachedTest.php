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

use Infection\Metrics\MaxTimeoutCountReached;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(MaxTimeoutCountReached::class)]
final class MaxTimeoutCountReachedTest extends TestCase
{
    public function test_it_extends_unexpected_value_exception(): void
    {
        $exception = MaxTimeoutCountReached::create(5, 10);

        $this->assertInstanceOf(UnexpectedValueException::class, $exception);
    }

    public function test_it_creates_exception_with_correct_message(): void
    {
        $exception = MaxTimeoutCountReached::create(5, 10);

        $this->assertSame(
            'The maximum allowed timeouts is 5, but 10 timed out. Reduce timeouts or increase the limit!',
            $exception->getMessage(),
        );
    }

    public function test_it_creates_exception_with_zero_limit(): void
    {
        $exception = MaxTimeoutCountReached::create(0, 1);

        $this->assertSame(
            'The maximum allowed timeouts is 0, but 1 timed out. Reduce timeouts or increase the limit!',
            $exception->getMessage(),
        );
    }
}
