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

namespace Infection\Tests\Configuration\ConfigurationFactory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigurationFactoryGit::class)]
final class ConfigurationFactoryGitTest extends TestCase
{
    /**
     * @param non-empty-string $diffFilter
     * @param non-empty-string $baseBranch
     * @param non-empty-string[] $sourceDirectories
     */
    #[DataProvider('changedFileRelativePathsProvider')]
    public function test_it_can_show_the_changed_file_relative_paths(
        string $changedFileRelativePaths,
        string $diffFilter,
        string $baseBranch,
        array $sourceDirectories,
        string $expected,
    ): void {
        $git = new ConfigurationFactoryGit('unknown', $changedFileRelativePaths);

        $actual = $git->getChangedFileRelativePaths(
            $diffFilter,
            $baseBranch,
            $sourceDirectories,
        );

        $this->assertSame($expected, $actual);
    }

    public static function changedFileRelativePathsProvider(): iterable
    {
        yield 'no source directories' => [
            'src/a.php,src/b.php',
            'AM',
            'main',
            [],
            'f(AM, main, []) = src/a.php,src/b.php',
        ];

        yield 'with source directories' => [
            'src/a.php,src/b.php',
            'AM',
            'main',
            ['src', 'config'],
            'f(AM, main, [src, config]) = src/a.php,src/b.php',
        ];
    }
}
