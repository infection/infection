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

namespace Infection\Tests\TestingUtility\FileSystem;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MockSplFileInfo::class)]
final class MockSplFileInfoTest extends TestCase
{
    public function test_it_can_be_created_for_a_real_file_from_a_path(): void
    {
        $fileInfo = new MockSplFileInfo(__FILE__);

        $this->assertSame(__FILE__, $fileInfo->getPathname());
        $this->assertFalse($fileInfo->getRealPath());
    }

    public function test_it_can_be_created_for_a_fake_file_from_a_path(): void
    {
        $fileInfo = new MockSplFileInfo('/path/to/file');

        $this->assertSame('/path/to/file', $fileInfo->getPathname());
        $this->assertFalse($fileInfo->getRealPath());

        $anotherFileInfo = new MockSplFileInfo('file');

        $this->assertSame('file', $anotherFileInfo->getPathname());
        $this->assertFalse($anotherFileInfo->getRealPath());
    }

    public function test_it_can_be_created_for_a_real_file_for_a_realpath(): void
    {
        $fileInfo = new MockSplFileInfo(realPath: __FILE__);

        $this->assertSame('file.txt', $fileInfo->getPathname());
        $this->assertSame(__FILE__, $fileInfo->getRealPath());
    }

    public function test_it_can_be_created_for_a_fake_file_for_a_realpath(): void
    {
        $fileInfo = new MockSplFileInfo(realPath: '/path/to/file');

        $this->assertSame('file.txt', $fileInfo->getPathname());
        $this->assertSame('/path/to/file', $fileInfo->getRealPath());

        $anotherFileInfo = new MockSplFileInfo(realPath: 'file');

        $this->assertSame('file.txt', $anotherFileInfo->getPathname());
        $this->assertSame('file', $anotherFileInfo->getRealPath());
    }
}
