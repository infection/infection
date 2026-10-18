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

namespace Infection\Tests\FileSystem;

use Infection\FileSystem\Paths;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Paths::class)]
final class PathsTest extends TestCase
{
    /**
     * @param string[] $paths
     * @param non-empty-string[] $expected
     */
    #[DataProvider('pathsProvider')]
    public function test_it_makes_paths_absolute(
        array $paths,
        string $basePath,
        array $expected,
        bool $canonicalize = false,
    ): void {
        $actual = Paths::makeAbsolute(
            $paths,
            $basePath,
            $canonicalize,
        );

        $this->assertSame($expected, $actual);
    }

    public static function pathsProvider(): iterable
    {
        yield 'no paths' => [
            [],
            '/project',
            [],
        ];

        yield 'relative paths are resolved against the base path' => [
            [
                'src',
                'my lib/../lib',
            ],
            '/project',
            [
                '/project/src',
                '/project/lib',
            ],
        ];

        yield 'absolute paths are preserved verbatim alongside relative paths' => [
            [
                '/other/../src/',
                'lib',
            ],
            '/project',
            [
                '/other/../src/',
                '/project/lib',
            ],
        ];

        yield 'array keys and order are preserved' => [
            [
                'source' => 'src',
                5 => 'lib',
            ],
            '/project',
            [
                'source' => '/project/src',
                5 => '/project/lib',
            ],
        ];

        yield 'an empty path resolves to the base path' => [
            [''],
            '/project',
            ['/project'],
        ];

        yield 'relative base paths remain supported' => [
            ['src'],
            'project',
            ['project/src'],
        ];

        yield 'canonicalization resolves both absolute and relative paths' => [
            [
                'source' => '/other/../src/',
                5 => 'my lib/../lib',
            ],
            '/project',
            [
                'source' => '/src',
                5 => '/project/lib',
            ],
            'canonicalize' => true,
        ];
    }

    public function test_it_rejects_an_empty_resolved_path(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Resolving a path against its base path must not produce an empty path.');

        Paths::makeAbsolute(
            [''],
            '',
        );
    }

    public function test_it_requires_an_absolute_base_path_when_canonicalizing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The base path "project" is not an absolute path.');

        Paths::makeAbsolute(
            ['src'],
            'project',
            canonicalize: true,
        );
    }
}
