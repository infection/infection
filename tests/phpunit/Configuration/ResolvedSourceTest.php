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

namespace Infection\Tests\Configuration;

use Infection\Configuration\Entry\Source;
use Infection\Configuration\ResolvedSource;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResolvedSource::class)]
final class ResolvedSourceTest extends TestCase
{
    /**
     * @param list<non-empty-string> $expectedDirectories
     */
    #[DataProvider('sourceProvider')]
    public function test_it_resolves_directories_and_preserves_exclusions(
        Source $source,
        string $basePath,
        array $expectedDirectories,
    ): void {
        $resolvedSource = ResolvedSource::create(
            $source,
            $basePath,
        );

        $this->assertSame(
            $expectedDirectories,
            $resolvedSource->directories,
        );
        $this->assertSame(
            $source->excludes,
            $resolvedSource->excludes,
        );
        $this->assertSame(
            $basePath,
            $resolvedSource->basePath,
        );
    }

    public static function sourceProvider(): iterable
    {
        yield 'empty source directories are supported' => [
            new Source(
                [],
                ['vendor'],
            ),
            '/project',
            [],
        ];

        yield 'relative directories resolve against the configuration directory' => [
            new Source(
                ['src/', '../shared', 'lib/../app'],
                ['vendor/', 'Generated.php', '/Generated/'],
            ),
            '/project/config',
            ['/project/config/src', '/project/shared', '/project/config/app'],
        ];

        yield 'absolute directories preserve segments that may traverse symbolic links' => [
            new Source(
                ['/external/link/..', '/external/./src/'],
                ['Generated.php'],
            ),
            '/project',
            ['/external/link/..', '/external/./src/'],
        ];

        yield 'absolute and relative directories can be mixed' => [
            new Source(
                ['src', '/shared/lib'],
                [],
            ),
            '/project',
            ['/project/src', '/shared/lib'],
        ];
    }

    #[DataProvider('invalidBasePathProvider')]
    public function test_it_rejects_a_non_absolute_base_path(string $basePath): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The source base path must be absolute so resolved directories do not depend on the working directory.',
        );

        ResolvedSource::create(
            new Source(
                ['src'],
                [],
            ),
            $basePath,
        );
    }

    public static function invalidBasePathProvider(): iterable
    {
        yield 'relative path' => ['project'];

        yield 'empty path' => [''];
    }
}
