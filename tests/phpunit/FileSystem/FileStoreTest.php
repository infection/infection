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

use Infection\FileSystem\FileStore;
use Infection\FileSystem\FileSystem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

#[CoversClass(FileStore::class)]
final class FileStoreTest extends TestCase
{
    private MockObject&FileSystem $fileSystemMock;

    private FileStore $fileStore;

    protected function setUp(): void
    {
        $this->fileSystemMock = $this->createMock(FileSystem::class);
        $this->fileStore = new FileStore($this->fileSystemMock);
    }

    public function test_it_reads_file_contents_from_a_path_and_caches_it(): void
    {
        $filePath = '/path/to/file.php';
        $expected = '<?php echo "Hello World";';

        $this->fileSystemMock
            ->expects($this->once())
            ->method('readFile')
            ->with($filePath)
            ->willReturn($expected);

        $actual1 = $this->fileStore->getContents($filePath);
        $actual2 = $this->fileStore->getContents($filePath);

        $this->assertSame($expected, $actual1);
        $this->assertSame($expected, $actual2);
    }

    public function test_it_reads_file_contents_from_spl_file_info(): void
    {
        $filePath = '/path/to/file.php';
        $expected = '<?php echo "Hello World";';

        $fileInfoStub = $this->createStub(SplFileInfo::class);
        $fileInfoStub
            ->method('getRealPath')
            ->willReturn($filePath);

        $this->fileSystemMock
            ->expects($this->once())
            ->method('readFile')
            ->with($filePath)
            ->willReturn($expected);

        $actual1 = $this->fileStore->getContents($fileInfoStub);
        $actual2 = $this->fileStore->getContents($fileInfoStub);

        $this->assertSame($expected, $actual1);
        $this->assertSame($expected, $actual2);
    }
}
