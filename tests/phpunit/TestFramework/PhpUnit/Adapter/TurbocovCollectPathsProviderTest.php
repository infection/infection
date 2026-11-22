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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use function Safe\ini_get;

#[CoversClass(TurbocovCollectPathsProvider::class)]
final class TurbocovCollectPathsProviderTest extends TestCase
{
    /**
     * @param non-empty-list<string> $sourceDirectoryPaths
     */
    #[DataProvider('sourceDirectoryPathsProvider')]
    public function test_it_provides_the_source_directories(
        array $sourceDirectoryPaths,
        ?string $iniValue,
        bool $expectedShouldProvide,
        string $expectedCollectPaths,
    ): void {
        $provider = new TurbocovCollectPathsProvider($sourceDirectoryPaths, $iniValue);

        $this->assertSame($expectedShouldProvide, $provider->shouldProvide());
        $this->assertSame($expectedCollectPaths, $provider->getCollectPaths());
    }

    #[RequiresPhpExtension('turbocov')]
    public function test_it_reads_the_collect_paths_from_the_ini_configuration(): void
    {
        $provider = new TurbocovCollectPathsProvider(['/project/src']);

        // Note that `turbocov.collect_paths` is applied when PHP compiles a
        // file, so it cannot be set at runtime.
        // As a result, this test is a bit light, but being more thorough would
        // be too expensive infrastructure-wise.
        $expected = ini_get('turbocov.collect_paths') === '';

        $this->assertSame($expected, $provider->shouldProvide());
    }

    public function test_it_does_not_provide_the_collect_paths_without_turbocov(): void
    {
        if (extension_loaded('turbocov')) {
            $this->markTestSkipped('Test requires turbocov to be disabled to run.');
        }

        $provider = new TurbocovCollectPathsProvider(['/project/src']);

        $this->assertFalse($provider->shouldProvide());
    }

    public static function sourceDirectoryPathsProvider(): iterable
    {
        yield 'one source directory path' => [
            'sourceDirectoryPaths' => ['/project/src'],
            'iniValue' => '',
            'expectedShouldProvide' => true,
            'expectedCollectPaths' => '/project/src',
        ];

        yield 'several source directory paths' => [
            'sourceDirectoryPaths' => [
                '/path/to/project/src',
                '/path/to/project/packages/package-a/src',
                '/elsewhere/lib',
            ],
            'iniValue' => '',
            'expectedShouldProvide' => true,
            'expectedCollectPaths' => '/path/to/project/src:/path/to/project/packages/package-a/src:/elsewhere/lib',
        ];

        yield 'configured collect paths' => [
            'sourceDirectoryPaths' => ['/project/src'],
            'iniValue' => 'src:config/services.php',
            'expectedShouldProvide' => false,
            'expectedCollectPaths' => '/project/src',
        ];
    }
}
