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

namespace Infection\Tests\Resource\Processor;

use Fidry\CpuCoreCounter\CpuCoreCounter;
use Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\NullCpuCoreFinder;
use Infection\Resource\Processor\CpuCoresCountProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;

#[CoversClass(CpuCoresCountProvider::class)]
final class CpuCoresCountProviderTest extends TestCase
{
    /**
     * @param positive-int $coreCount
     * @param positive-int|null $limit
     */
    #[DataProvider('cpuCountProvider')]
    public function test_it_provides_and_logs_the_available_cpu_count(
        int $coreCount,
        ?int $limit,
        float $loadLimit,
        int $expected,
    ): void {
        $expectedRecords = [
            [
                'level' => LogLevel::DEBUG,
                'message' => 'CPU core count: {cores}; CPU usage ratio: {ratio}; CPU count limit: {limit}; available CPUs used: {available}.',
                'context' => [
                    'cores' => $coreCount,
                    'ratio' => $loadLimit,
                    'limit' => $limit ?? 'none',
                    'available' => $expected,
                ],
            ],
        ];

        $logger = new TestLogger();
        $provider = new CpuCoresCountProvider(
            new CpuCoreCounter(
                finders: [
                    new DummyCpuCoreFinder($coreCount),
                ],
                countLimitFinder: $limit === null
                    ? new NullCpuCoreFinder()
                    : new DummyCpuCoreFinder($limit),
            ),
            $logger,
        );

        $actual = $provider->provide($loadLimit);

        $this->assertSame($expected, $actual);
        $this->assertSame($expectedRecords, $logger->records);
    }

    public static function cpuCountProvider(): iterable
    {
        yield 'rounding down after applying the load limit' => [
            'coreCount' => 8,
            'limit' => null,
            'loadLimit' => 0.8,
            'expected' => 6,
        ];

        yield 'custom load limit' => [
            'coreCount' => 8,
            'limit' => null,
            'loadLimit' => 0.5,
            'expected' => 4,
        ];

        yield 'CPU count limit below the available count' => [
            'coreCount' => 8,
            'limit' => 4,
            'loadLimit' => 0.8,
            'expected' => 4,
        ];

        yield 'CPU count limit above the available count' => [
            'coreCount' => 8,
            'limit' => 16,
            'loadLimit' => 0.8,
            'expected' => 6,
        ];

        yield 'minimum of one CPU' => [
            'coreCount' => 1,
            'limit' => null,
            'loadLimit' => 0.8,
            'expected' => 1,
        ];

        yield 'all CPUs available with a load limit of one' => [
            'coreCount' => 8,
            'limit' => null,
            'loadLimit' => 1.0,
            'expected' => 8,
        ];
    }
}
