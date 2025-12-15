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

namespace Infection\Tests\Source\Matcher;

use Infection\Differ\ChangedLinesRange;
use Infection\FileSystem\FileSystem;
use Infection\Git\Git;
use Infection\Source\Matcher\GitDiffSourceLineMatcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function sprintf;

#[CoversClass(GitDiffSourceLineMatcher::class)]
final class GitDiffSourceLineMatcherTest extends TestCase
{
    private FileSystem&MockObject $fileSystemStub;

    protected function setUp(): void
    {
        $this->fileSystemStub = $this->createMock(FileSystem::class);
        $this->fileSystemStub
            ->method('realPath')
            ->willReturnCallback(
                static fn (string $path): string => '/path/to/' . $path,
            );
    }

    public function test_it_memoizes_parsed_results(): void
    {
        $matcher = new GitDiffSourceLineMatcher(
            $this->createGitStub([]),
            $this->fileSystemStub,
            'main',
            'AM',
            ['src', 'lib'],
        );

        $matcher->touches('/path/to/File.php', 1, 1);

        // the second call should reuse memoized results cached previously
        $matcher->touches('/path/to/File.php', 1, 1);
    }

    /**
     * @param array<string, list<ChangedLinesRange>> $changedLinesRangesByFilePathname
     * @param positive-int $mutationStartLine
     * @param positive-int $mutationEndLine
     */
    #[DataProvider('provideLines')]
    public function test_it_tells_if_the_mutation_touches_any_of_the_changed_lines(
        array $changedLinesRangesByFilePathname,
        string $fileRealPath,
        int $mutationStartLine,
        int $mutationEndLine,
        bool $expected,
    ): void {
        $matcher = new GitDiffSourceLineMatcher(
            $this->createGitStub($changedLinesRangesByFilePathname),
            $this->fileSystemStub,
            'main',
            'AM',
            ['src', 'lib'],
        );

        $actual = $matcher->touches(
            $fileRealPath,
            $mutationStartLine,
            $mutationEndLine,
        );

        $this->assertSame(
            $expected,
            $actual,
            sprintf('Line %d was not found in diff', $mutationStartLine),
        );
    }

    public static function provideLines(): iterable
    {
        yield 'the mutation touches no changed line' => [
            [
                'src/File.php' => [ChangedLinesRange::forLine(3)],
            ],
            '/path/to/src/File.php',
            1,
            1,
            false,
        ];

        yield 'the mutation touches a changed line' => [
            [
                'src/File.php' => [ChangedLinesRange::forLine(3)],
            ],
            '/path/to/src/File.php',
            2,
            5,
            true,
        ];

        yield 'the mutation touches none of the changed lines' => [
            [
                'src/File1.php' => [
                    ChangedLinesRange::forLine(10),
                    ChangedLinesRange::create(30, 50),
                ],
            ],
            '/path/to/src/File2.php',
            12,
            15,
            false,
        ];

        yield 'the mutation touches one of the changed lines' => [
            [
                'src/File1.php' => [
                    ChangedLinesRange::forLine(10),
                    ChangedLinesRange::create(30, 50),
                ],
            ],
            '/path/to/src/File1.php',
            4,
            12,
            true,
        ];

        yield 'the mutation touches one of the changed lines of a different file' => [
            [
                'src/File1.php' => [
                    ChangedLinesRange::forLine(10),
                    ChangedLinesRange::create(30, 50),
                ],
                'src/File2.php' => [
                    ChangedLinesRange::create(1, 1),
                    ChangedLinesRange::create(3, 5),
                ],
            ],
            '/path/to/src/File1.php',
            1,
            4,
            false,
        ];
    }

    /**
     * @param array<string, list<ChangedLinesRange>> $changedLinesRangesByFilePathname
     */
    private function createGitStub(array $changedLinesRangesByFilePathname): Git&MockObject
    {
        /** @var Git&MockObject $git */
        $git = $this->createMock(Git::class);
        $git
            ->expects($this->once())
            ->method('getChangedLinesRangesByFileRelativePaths')
            ->with('AM', 'main', ['src', 'lib'])
            ->willReturn($changedLinesRangesByFilePathname);

        return $git;
    }
}
