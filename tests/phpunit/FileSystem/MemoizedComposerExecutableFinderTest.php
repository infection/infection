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

use Infection\FileSystem\Finder\ComposerExecutableFinder;
use Infection\FileSystem\Finder\Exception\FinderException;
use Infection\FileSystem\Finder\MemoizedComposerExecutableFinder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MemoizedComposerExecutableFinder::class)]
final class MemoizedComposerExecutableFinderTest extends TestCase
{
    public function test_it(): void
    {
        $pathToComposer = '/path/to/composer';

        $mockFinder = $this->createMock(ComposerExecutableFinder::class);
        $mockFinder->expects($this->once())
            ->method('find')
            ->willReturn($pathToComposer);

        $finder = new MemoizedComposerExecutableFinder($mockFinder);

        $this->assertSame($pathToComposer, $finder->find());
        $this->assertSame($pathToComposer, $finder->find());
    }

    public function test_it_throws(): void
    {
        $exception = FinderException::composerNotFound();

        $mockFinder = $this->createMock(ComposerExecutableFinder::class);
        $mockFinder->expects($this->once())
            ->method('find')
            ->willThrowException($exception);

        $finder = new MemoizedComposerExecutableFinder($mockFinder);

        $this->expectExceptionObject($exception);
        $finder->find();
    }
}
