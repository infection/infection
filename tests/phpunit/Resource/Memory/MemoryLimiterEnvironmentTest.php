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

use Infection\Resource\Memory\MemoryLimiterEnvironment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function Safe\ini_get;
use function Safe\ini_set;

#[CoversClass(MemoryLimiterEnvironment::class)]
final class MemoryLimiterEnvironmentTest extends TestCase
{
    private string $originalMemoryLimit;

    private MemoryLimiterEnvironment $environment;

    protected function setUp(): void
    {
        $this->originalMemoryLimit = ini_get('memory_limit');

        $this->environment = new MemoryLimiterEnvironment();
    }

    protected function tearDown(): void
    {
        ini_set('memory_limit', $this->originalMemoryLimit);
    }

    #[DataProvider('memoryLimitProvider')]
    public function test_it_can_detect_if_a_memory_limit_is_set(string $memoryLimit, bool $expected): void
    {
        @ini_set('memory_limit', $memoryLimit);

        $this->assertSame($expected, $this->environment->hasMemoryLimitSet());
    }

    public static function memoryLimitProvider(): iterable
    {
        yield 'no limit' => [
            '-1',
            false,
        ];

        yield 'limit' => [
            '512M',
            true,
        ];

        yield 'invalid limit' => [
            '-512M',
            true,
        ];

        yield 'limit without unit' => [
            '1073741824',   // 1G
            true,
        ];
    }
}
