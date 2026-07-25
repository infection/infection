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

namespace Infection\Tests\Report\Framework\Writer;

use Infection\FileSystem\FileSystem;
use Infection\Report\Framework\Writer\FileWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileWriter::class)]
final class FileWriterTest extends TestCase
{
    /**
     * @param iterable<string>|string $contentOrLines
     */
    #[DataProvider('contentsOrLinesProvider')]
    public function test_it_can_write_contents_or_lines_to_the_file(
        iterable|string $contentOrLines,
        string $expected,
    ): void {
        $filePath = '/path/to/file.log';

        $fileSystemMock = $this->createMock(FileSystem::class);
        $fileSystemMock
            ->expects($this->once())
            ->method('dumpFile')
            ->with($filePath, $expected);

        $writer = new FileWriter(
            $fileSystemMock,
            $filePath,
        );
        $writer->write($contentOrLines);
    }

    public static function contentsOrLinesProvider(): iterable
    {
        yield 'contents' => [
            'Hello World!',
            'Hello World!',
        ];

        yield 'lines' => [
            [
                'First line',
                'Second line',
            ],
            <<<'EOF'
                First line
                Second line
                EOF,
        ];
    }
}
