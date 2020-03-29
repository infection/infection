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

namespace Infection\Tests\Resource\Memory;

use Infection\Resource\Memory\MemoryFormatter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class MemoryFormatterTest extends TestCase
{
    /**
     * @var MemoryFormatter
     */
    private $memoryFormatter;

    protected function setUp(): void
    {
        $this->memoryFormatter = new MemoryFormatter();
    }

    /**
     * @dataProvider bytesProvider
     */
    public function test_it_converts_bytes_to_human_readable_time(float $bytes, string $expectedString): void
    {
        $timeString = $this->memoryFormatter->toHumanReadableString($bytes);

        $this->assertSame($expectedString, $timeString);
    }

    public function test_it_cannot_convert_negative_bytes(): void
    {
        try {
            $this->memoryFormatter->toHumanReadableString(-1.);

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Expected a positive or null amount of bytes. Got: -1',
                $exception->getMessage()
            );
        }
    }

    public function bytesProvider(): iterable
    {
        yield [0., '0.00B'];

        yield [10., '10.00B'];

        yield [512., '0.50KB'];

        yield [768., '0.75KB'];

        yield [1024., '1.00KB'];

        yield [1024 ** 2, '1.00MB'];

        yield [1024 ** 3, '1.00GB'];

        yield [1024 ** 4, '1.00TB'];

        yield [1024 ** 5, '1.00PB'];

        yield [1024 ** 6, '1.00EB'];
    }
}
