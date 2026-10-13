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

namespace Infection\Tests\Command\Option;

use Infection\Command\Option\ThreadsOption;
use Infection\Console\IO;
use Infection\Tests\Command\CommandOptionTestCase;
use Infection\Tests\Fixtures\Resource\Processor\DummyCpuCoresCountProvider;
use Infection\Tests\TestingUtility\Console\Command\TestOptionCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

#[CoversClass(ThreadsOption::class)]
final class ThreadsOptionTest extends CommandOptionTestCase
{
    // TODO: this is necessary because we need to inject DummyCpuCoresCountProvider (for now).
    //  This will be addressed in a follow up PR.
    #[DataProvider('maxThreadCountProvider')]
    public function test_it_leaves_one_cpu_core_available(
        int $cpuCoresCount,
        int $expected,
    ): void {
        $io = new IO(
            new StringInput('--threads=max'),
            new NullOutput(),
        );

        $cpuCoresCountProvider = new DummyCpuCoresCountProvider($cpuCoresCount);

        TestOptionCommand::bind(
            ThreadsOption::class,
            $io,
        );

        $actual = ThreadsOption::get($io, $cpuCoresCountProvider);

        $this->assertSame($expected, $actual);
    }

    public static function optionProvider(): iterable
    {
        yield 'not provided' => [
            new StringInput(''),
            null,
        ];

        yield 'numeric string' => [
            new StringInput('--threads=5'),
            5,
        ];
    }

    public static function maxThreadCountProvider(): iterable
    {
        yield '1 CPU core stays at minimum of 1' => [
            1,
            1,
        ];

        yield '2 CPU cores is 1' => [
            2,
            1,
        ];

        yield '8 CPU cores is 7' => [
            8,
            7,
        ];
    }

    protected function getOptionClassName(): string
    {
        return ThreadsOption::class;
    }
}
