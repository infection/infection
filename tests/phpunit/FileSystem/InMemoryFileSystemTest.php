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

use Infection\FileSystem\InMemoryFileSystem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\IOException;

#[Group('integration')]
#[CoversClass(InMemoryFileSystem::class)]
final class InMemoryFileSystemTest extends TestCase
{
    private InMemoryFileSystem $fileSystem;

    protected function setUp(): void
    {
        $this->fileSystem = new InMemoryFileSystem();
    }

    public function test_a_file_is_not_readable_when_it_has_not_been_dumped(): void
    {
        $this->assertFalse($this->fileSystem->isReadableFile('/path/to/file.php'));
    }

    public function test_it_can_dump_and_read_a_file(): void
    {
        $filename = '/path/to/file.php';
        $content = '<?php echo "Hello";';

        $this->fileSystem->dumpFile($filename, $content);

        $this->assertTrue($this->fileSystem->isReadableFile($filename));
        $this->assertSame($content, $this->fileSystem->readFile($filename));
    }

    public function test_it_overwrites_existing_files_on_dump(): void
    {
        $filename = '/path/to/file.php';

        $this->fileSystem->dumpFile($filename, 'first');
        $this->fileSystem->dumpFile($filename, 'second');

        $this->assertSame('second', $this->fileSystem->readFile($filename));
    }

    public function test_it_tracks_files_independently(): void
    {
        $this->fileSystem->dumpFile('/file1', 'content1');
        $this->fileSystem->dumpFile('/file2', 'content2');

        $this->assertSame('content1', $this->fileSystem->readFile('/file1'));
        $this->assertSame('content2', $this->fileSystem->readFile('/file2'));
    }

    public function test_it_throws_when_reading_an_unknown_file(): void
    {
        $this->expectException(IOException::class);
        $this->expectExceptionMessage('Failed to read file "/unknown": File is a directory.');

        $this->fileSystem->readFile('/unknown');
    }
}
