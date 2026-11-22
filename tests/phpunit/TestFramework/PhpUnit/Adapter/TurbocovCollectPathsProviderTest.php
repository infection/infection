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
    #[DataProvider('shouldProvideProvider')]
    public function test_it_tells_if_the_collect_paths_should_be_provided(
        string $initialTestsPhpOptions,
        ?string $phpConfiguredCollectPaths,
        bool $expected,
    ): void {
        $provider = new TurbocovCollectPathsProvider(
            ['/project/src'],
            $initialTestsPhpOptions,
            $phpConfiguredCollectPaths,
        );

        $this->assertSame($expected, $provider->shouldProvide());
    }

    public static function shouldProvideProvider(): iterable
    {
        yield 'turbocov not loaded' => [
            'initialTestsPhpOptions' => '',
            'phpConfiguredCollectPaths' => null,
            'expected' => false,
        ];

        yield 'turbocov loaded without collect paths' => [
            'initialTestsPhpOptions' => '',
            'phpConfiguredCollectPaths' => '',
            'expected' => true,
        ];

        yield 'turbocov loaded with configured collect paths' => [
            'initialTestsPhpOptions' => '',
            'phpConfiguredCollectPaths' => 'src',
            'expected' => false,
        ];

        yield 'turbocov loaded with collect paths set for the initial test run' => [
            'initialTestsPhpOptions' => '-d memory_limit=-1 -d turbocov.collect_paths=@paths.txt',
            'phpConfiguredCollectPaths' => '',
            'expected' => false,
        ];

        yield 'turbocov loaded for the initial test run only' => [
            'initialTestsPhpOptions' => '-d memory_limit=-1 -d extension=/path/to/turbocov.so',
            'phpConfiguredCollectPaths' => null,
            'expected' => true,
        ];

        yield 'turbocov loaded for the initial test run only with collect paths' => [
            'initialTestsPhpOptions' => '-d extension=turbocov -d turbocov.collect_paths=src',
            'phpConfiguredCollectPaths' => null,
            'expected' => false,
        ];

        yield 'another extension loaded for the initial test run' => [
            'initialTestsPhpOptions' => '-d extension=pcov.so -d pcov.directory=src',
            'phpConfiguredCollectPaths' => null,
            'expected' => false,
        ];
    }

    /**
     * @param non-empty-array<string> $sourceDirectoryPaths
     */
    #[DataProvider('sourceDirectoryPathsProvider')]
    public function test_it_provides_the_source_directories(
        array $sourceDirectoryPaths,
        string $expected,
    ): void {
        $provider = new TurbocovCollectPathsProvider($sourceDirectoryPaths, '', '');

        $this->assertSame($expected, $provider->getCollectPaths());
    }

    public static function sourceDirectoryPathsProvider(): iterable
    {
        yield 'one source directory path' => [
            'sourceDirectoryPaths' => ['/project/src'],
            'expected' => '/project/src',
        ];

        yield 'several source directory paths' => [
            'sourceDirectoryPaths' => [
                '/path/to/project/src',
                '/path/to/project/packages/package-a/src',
                '/elsewhere/lib',
            ],
            'expected' => '/path/to/project/src:/path/to/project/packages/package-a/src:/elsewhere/lib',
        ];

        yield 'source directory paths with spaces' => [
            'sourceDirectoryPaths' => ['/path/to/my project/src'],
            'expected' => '/path/to/my project/src',
        ];
    }

    public function test_it_throws_when_a_source_directory_contains_the_path_separator(): void
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
    public function test_it_reads_the_collect_paths_from_the_ini_configuration(): void
    {
        $provider = TurbocovCollectPathsProvider::create(['/project/src'], null);

        // `turbocov.collect_paths` is applied when PHP compiles a file, so it
        // cannot be set at runtime: this test depends on the PHP configuration.
        $expected = ini_get('turbocov.collect_paths') === '';

        $this->assertSame($expected, $provider->shouldProvide());
    }

    public function test_it_does_not_provide_the_collect_paths_without_turbocov(): void
    {
        if (extension_loaded('turbocov')) {
            $this->markTestSkipped('Test requires turbocov to be disabled to run.');
        }

        $provider = TurbocovCollectPathsProvider::create(['/project/src'], null);

        $this->assertFalse($provider->shouldProvide());
    }
}
