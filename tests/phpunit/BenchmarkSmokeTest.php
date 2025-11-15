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

use Infection\Framework\OperatingSystem;
use Infection\Testing\StringNormalizer;
use Infection\Tests\TestingUtility\Process\TestPhpExecutableFinder;
use function is_dir;
use const PHP_SAPI;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Process;

/**
 * Smoke test to ensure the benchmark profile scripts somewhat work.
 */
#[Group('benchmark')]
#[CoversNothing]
final class BenchmarkSmokeTest extends TestCase
{
    private const BENCHMARK_DIR = __DIR__ . '/../benchmark';

    /**
     * @param non-empty-list<string> $command
     */
    #[DataProvider('provideBenchmarks')]
    public function test_all_the_benchmarks_can_be_executed(
        array $command,
        string $sourcesLocation,
        string $expectedOutput,
    ): void {
        if (OperatingSystem::isWindows()) {
            $this->markTestSkipped('Not interested in profiling on Windows.');
        }

        if (PHP_SAPI === 'phpdbg') {
            $this->markTestSkipped('This test requires running without PHPDBG.');
        }

        if (!is_dir($sourcesLocation)) {
            $this->markTestIncomplete('Benchmark requires sources to be prepared.');
        }

        $path = $command[0];

        $this->assertFileExists($path);

        $benchmarkProcess = new Process([
            TestPhpExecutableFinder::find(),
            ...$command,
        ]);
        $benchmarkProcess->mustRun();

        $actualOutput = StringNormalizer::normalizeString($benchmarkProcess->getOutput());

        $this->assertStringContainsString($expectedOutput, $actualOutput);
    }

    public static function provideBenchmarks(): iterable
    {
        yield 'MutationGenerator' => [
            [
                Path::canonicalize(self::BENCHMARK_DIR . '/MutationGenerator/profile.php'),
                '--max-mutation-count=1',
                '--debug',
            ],
            self::BENCHMARK_DIR . '/MutationGenerator/sources',
            <<<'STDOUT'
                1 mutation(s) generated.

                STDOUT,
        ];

        yield 'Tracing' => [
            [
                Path::canonicalize(self::BENCHMARK_DIR . '/Tracing/profile.php'),
                '--max-trace-count=1',
                '--debug',
            ],
            self::BENCHMARK_DIR . '/Tracing/coverage',
            <<<'STDOUT'
                1 trace(s) generated.

                STDOUT,
        ];
    }
}
