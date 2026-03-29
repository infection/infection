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

    public function test_it_reads_file_contents_from_a_path_and_caches_it_until_a_new_file_is_requested(): void
    {
        $filePath1 = '/path/to/file1.php';
        $fileContent1 = '<?php echo "Uno";';

        $filePath2 = '/path/to/file2.php';
        $fileContent2 = '<?php echo "Secundo";';

        $this->fileSystemMock
            ->expects($this->exactly(2))
            ->method('readFile')
            ->willReturnOnConsecutiveCalls(
                $fileContent1,
                $fileContent2,
            );

        $actual1 = $this->fileStore->getContents($filePath1);
        $actual2 = $this->fileStore->getContents($filePath1);

        $this->assertSame($fileContent1, $actual1);
        $this->assertSame($fileContent1, $actual2);

        $actual3 = $this->fileStore->getContents($filePath2);
        $actual4 = $this->fileStore->getContents($filePath2);

        $this->assertSame($fileContent2, $actual3);
        $this->assertSame($fileContent2, $actual4);
    }

    public function test_it_reads_file_contents_from_spl_file_info_and_caches_it_until_a_new_file_is_requested(): void
    {
        $filePath1 = '/path/to/file.php';
        $fileContent1 = '<?php echo "Hello World";';

        $fileInfoStub1 = $this->createStub(SplFileInfo::class);
        $fileInfoStub1
            ->method('getRealPath')
            ->willReturn($filePath1);

        $filePath2 = '/path/to/file2.php';
        $fileContent2 = '<?php echo "Secundo";';

        $fileInfoStub2 = $this->createStub(SplFileInfo::class);
        $fileInfoStub2
            ->method('getRealPath')
            ->willReturn($filePath2);

        $this->fileSystemMock
            ->expects($this->exactly(2))
            ->method('readFile')
            ->willReturnOnConsecutiveCalls(
                $fileContent1,
                $fileContent2,
            );

        $actual1 = $this->fileStore->getContents($fileInfoStub1);
        $actual2 = $this->fileStore->getContents($fileInfoStub1);

        $this->assertSame($fileContent1, $actual1);
        $this->assertSame($fileContent1, $actual2);

        $actual3 = $this->fileStore->getContents($fileInfoStub2);
        $actual4 = $this->fileStore->getContents($fileInfoStub2);

        $this->assertSame($fileContent2, $actual3);
        $this->assertSame($fileContent2, $actual4);
    }
}
