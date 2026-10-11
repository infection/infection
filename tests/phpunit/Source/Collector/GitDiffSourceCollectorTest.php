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

namespace Infection\Tests\Source\Collector;

use Infection\Configuration\SourceFilter\GitDiffFilter;
use Infection\Configuration\SourceFilter\PlainFilter;
use Infection\Git\Git;
use Infection\Source\Collector\BasicSourceCollector;
use Infection\Source\Collector\GitDiffSourceCollector;
use Infection\Source\Collector\SourceCollector;
use Infection\Source\Exception\NoSourceFound;
use Infection\Testing\FileSystem\MockSplFileInfo;
use Infection\Tests\TestingUtility\PHPUnit\ExpectsThrowables;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(GitDiffSourceCollector::class)]
#[Group('integration')]
final class GitDiffSourceCollectorTest extends TestCase
{
    use ExpectsThrowables;

    public function test_it_collects_source_files_from_the_inner_collector(): void
    {
        $expected = [
            'src/Foo.php' => new MockSplFileInfo('src/Foo.php'),
        ];

        $innerCollector = $this->createMock(SourceCollector::class);
        $innerCollector
            ->expects($this->once())
            ->method('collect')
            ->willReturn($expected)
        ;

        $collector = new GitDiffSourceCollector(
            $innerCollector,
            new GitDiffFilter('AM', 'main'),
        );

        $actual = $collector->collect();

        $this->assertSame($expected, $actual);
    }

    public function test_it_creates_a_collector_with_the_git_diff_filter(): void
    {
        $filter = new GitDiffFilter('AM', 'main');

        $git = $this->createMock(Git::class);
        $git->expects($this->once())
            ->method('getChangedFilePaths')
            ->with('AM', 'main', ['src', 'lib'], '/project')
            ->willReturn(['src/README.md'])
        ;

        $actual = GitDiffSourceCollector::create(
            $git,
            '/project/infection.json5',
            ['src', 'lib'],
            ['Excluded.php'],
            $filter,
        );

        $expected = new GitDiffSourceCollector(
            new BasicSourceCollector(
                ['/project/src', '/project/lib'],
                ['Excluded.php'],
                new PlainFilter(['src/README.md']),
            ),
            $filter,
        );

        $this->assertEquals($expected, $actual);
    }

    public function test_it_adds_git_filter_context_when_no_source_file_is_found(): void
    {
        $previous = NoSourceFound::noSourceFileFound(
            new PlainFilter(['src/README.md']),
        );

        $innerCollector = $this->createMock(SourceCollector::class);
        $innerCollector
            ->expects($this->once())
            ->method('collect')
            ->willThrowException($previous)
        ;

        $collector = new GitDiffSourceCollector(
            $innerCollector,
            new GitDiffFilter('AM', 'main'),
        );

        $expected = NoSourceFound::noSourceFileFoundForGitDiff('AM', 'main', $previous);

        $actual = $this->expectToThrow(static fn () => $collector->collect());

        $this->assertEquals($expected, $actual);
        $this->assertSame($previous, $actual->getPrevious());
    }
}
