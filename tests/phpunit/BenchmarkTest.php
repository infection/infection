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

namespace Infection\Tests;

use function is_dir;
use const PHP_OS_FAMILY;
use const PHP_SAPI;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\realpath;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

#[Group('integration')]
#[CoversNothing]
final class BenchmarkTest extends TestCase
{
    private const BENCHMARK_DIR = __DIR__ . '/../benchmark';

    /**
     * @var string|null
     */
    private $phpExecutable;

    #[DataProvider('provideBenchmarks')]
    public function test_all_the_benchmarks_can_be_executed(string $path, string $sourcesLocation): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Not interested in profiling on Windows');
        }

        if (PHP_SAPI === 'phpdbg') {
            $this->markTestSkipped('This test requires running without PHPDBG');
        }

        if (!is_dir($sourcesLocation)) {
            $this->markTestIncomplete('Benchmark requires uncompressed sources');
        }

        $this->assertFileExists($path);

        $benchmarkProcess = new Process([
            $this->getPhpExecutable(),
            $path,
            '1',
        ]);

        $benchmarkProcess->run();

        if (!$benchmarkProcess->isSuccessful()) {
            throw new ProcessFailedException($benchmarkProcess);
        }
    }

    public static function provideBenchmarks(): iterable
    {
        yield 'MutationGenerator' => [
            realpath(self::BENCHMARK_DIR . '/MutationGenerator/generate-mutations.php'),
            self::BENCHMARK_DIR . '/MutationGenerator/sources',
        ];

        yield 'Tracing' => [
            realpath(self::BENCHMARK_DIR . '/Tracing/provide-traces.php'),
            self::BENCHMARK_DIR . '/Tracing/sources',
        ];
    }

    private function getPhpExecutable(): string
    {
        return $this->phpExecutable ??= (new PhpExecutableFinder())->find();
    }
}
