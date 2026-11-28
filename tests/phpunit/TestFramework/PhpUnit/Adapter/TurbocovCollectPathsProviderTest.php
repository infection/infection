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

namespace Infection\Tests\TestFramework\PhpUnit\Adapter;

use function extension_loaded;
use Infection\TestFramework\PhpUnit\Adapter\TurbocovCollectPathsProvider;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use function Safe\ini_get;

#[CoversClass(TurbocovCollectPathsProvider::class)]
final class TurbocovCollectPathsProviderTest extends TestCase
{
    private const array SOURCE_DIRECTORIES = ['/project/src', '/project/lib'];

    #[DataProvider('collectPathsProvider')]
    public function test_it_passes_the_collect_paths_to_the_initial_test_run(
        ?string $infectionProcessCollectPaths,
        string $initialTestsPhpOptions,
        ?string $expected,
    ): void {
        $provider = new TurbocovCollectPathsProvider(
            self::SOURCE_DIRECTORIES,
            $initialTestsPhpOptions,
            $infectionProcessCollectPaths,
        );

        $actual = $provider->shouldProvide()
            ? $provider->getCollectPaths()
            : null;

        $this->assertSame($expected, $actual);
    }

    public static function collectPathsProvider(): iterable
    {
        yield 'turbocov not loaded anywhere: nothing passed' => [
            'infectionProcessCollectPaths' => null,
            'initialTestsPhpOptions' => '',
            'expected' => null,
        ];

        yield 'another extension loaded for the initial test run: nothing passed' => [
            'infectionProcessCollectPaths' => null,
            'initialTestsPhpOptions' => '-d extension=pcov.so -d pcov.directory=src',
            'expected' => null,
        ];

        yield 'turbocov loaded without collect paths: source directories passed' => [
            'infectionProcessCollectPaths' => '',
            'initialTestsPhpOptions' => '',
            'expected' => '/project/src:/project/lib',
        ];

        yield 'turbocov loaded with collect paths (php.ini or `php -d` for Infection): forwarded as is' => [
            'infectionProcessCollectPaths' => 'src:config/services.php',
            'initialTestsPhpOptions' => '',
            'expected' => 'src:config/services.php',
        ];

        yield 'turbocov loaded, collect paths set in the initial tests PHP options: nothing passed, the option is used' => [
            'infectionProcessCollectPaths' => '',
            'initialTestsPhpOptions' => '-d memory_limit=-1 -d turbocov.collect_paths=@paths.txt',
            'expected' => null,
        ];

        yield 'turbocov loaded with collect paths, also set in the initial tests PHP options: nothing passed, the option wins' => [
            'infectionProcessCollectPaths' => 'src',
            'initialTestsPhpOptions' => '-d turbocov.collect_paths=@paths.txt',
            'expected' => null,
        ];

        yield 'turbocov loaded in the initial tests PHP options only: source directories passed' => [
            'infectionProcessCollectPaths' => null,
            'initialTestsPhpOptions' => '-d memory_limit=-1 -d extension=/path/to/turbocov.so',
            'expected' => '/project/src:/project/lib',
        ];

        yield 'turbocov and collect paths both in the initial tests PHP options: nothing passed, the option is used' => [
            'infectionProcessCollectPaths' => null,
            'initialTestsPhpOptions' => '-d extension=turbocov -d turbocov.collect_paths=src',
            'expected' => null,
        ];
    }

    /**
     * @param non-empty-array<string> $sourceDirectoryPaths
     */
    #[DataProvider('sourceDirectoryPathsProvider')]
    public function test_it_joins_the_source_directories_with_the_turbocov_path_separator(
        array $sourceDirectoryPaths,
        string $expected,
    ): void {
        $provider = new TurbocovCollectPathsProvider($sourceDirectoryPaths, '', '');

        $this->assertSame($expected, $provider->getCollectPaths());
    }

    public static function sourceDirectoryPathsProvider(): iterable
    {
        yield 'one source directory' => [
            'sourceDirectoryPaths' => ['/project/src'],
            'expected' => '/project/src',
        ];

        yield 'several source directories' => [
            'sourceDirectoryPaths' => [
                '/path/to/project/src',
                '/path/to/project/packages/package-a/src',
                '/elsewhere/lib',
            ],
            'expected' => '/path/to/project/src:/path/to/project/packages/package-a/src:/elsewhere/lib',
        ];

        yield 'source directory with spaces' => [
            'sourceDirectoryPaths' => ['/path/to/my project/src'],
            'expected' => '/path/to/my project/src',
        ];
    }

    public function test_it_rejects_a_source_directory_containing_the_turbocov_path_separator(): void
    {
        $provider = new TurbocovCollectPathsProvider(
            ['/project/src', '/project/a:b/src'],
            '',
            '',
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot configure turbocov coverage: the source directory "/project/a:b/src" contains ":"');

        $provider->getCollectPaths();
    }

    #[RequiresPhpExtension('turbocov')]
    public function test_it_reads_the_collect_paths_of_the_infection_process_when_turbocov_is_loaded(): void
    {
        $provider = TurbocovCollectPathsProvider::create(['/project/src'], null);

        // `turbocov.collect_paths` is applied when PHP compiles a file, so it
        // cannot be set at runtime: the expected value depends on the PHP configuration.
        $expected = ini_get('turbocov.collect_paths') === ''
            ? '/project/src'
            : ini_get('turbocov.collect_paths');

        $this->assertTrue($provider->shouldProvide());
        $this->assertSame($expected, $provider->getCollectPaths());
    }

    public function test_it_passes_nothing_when_turbocov_is_not_loaded_in_the_infection_process(): void
    {
        if (extension_loaded('turbocov')) {
            $this->markTestSkipped('Test requires turbocov to be disabled to run.');
        }

        $provider = TurbocovCollectPathsProvider::create(['/project/src'], null);

        $this->assertFalse($provider->shouldProvide());
    }
}
