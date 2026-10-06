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
use Infection\Tests\FileSystem\FileSystemTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use function Safe\ob_get_clean;
use function Safe\ob_start;

#[CoversClass(FileWriter::class)]
#[Group('integration')]
final class FileWriterTest extends FileSystemTestCase
{
    /**
     * @param iterable<string>|string $contentOrLines
     */
    #[DataProvider('contentsOrLinesProvider')]
    public function test_it_can_write_contents_or_lines_to_the_file(
        iterable|string $contentOrLines,
        string $expected,
    ): void {
        $filePath = $this->tmp . '/nested/file.log';
        $fileSystem = new FileSystem();
        $writer = new FileWriter(
            $fileSystem,
            $filePath,
            append: false,
        );
        $writer->write($contentOrLines);

        $this->assertSame(
            $expected,
            $fileSystem->readFile($filePath),
            'The writer must preserve the content and join iterable lines with newlines.',
        );
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

    #[DataProvider('writeModesProvider')]
    public function test_it_replaces_the_previous_run_and_applies_the_write_mode(
        bool $append,
        string $expected,
    ): void {
        $fileSystem = new FileSystem();
        $filePath = $this->tmp . '/file.log';
        $fileSystem->dumpFile(
            $filePath,
            'Previous run',
        );
        $writer = new FileWriter(
            $fileSystem,
            $filePath,
            $append,
        );

        $writer->write("First\n");

        $this->assertSame(
            "First\n",
            $fileSystem->readFile($filePath),
            'The first write must replace data from the previous run in either mode.',
        );

        $writer->write("Second\n");
        $writer->write("Third\n");

        $this->assertSame(
            $expected,
            $fileSystem->readFile($filePath),
            'Later writes must follow the selected append or replace mode.',
        );
    }

    public static function writeModesProvider(): iterable
    {
        yield 'replace on each write' => [
            false,
            "Third\n",
        ];

        yield 'append after the first write' => [
            true,
            "First\nSecond\nThird\n",
        ];
    }

    public function test_it_can_write_raw_content_to_the_php_output_stream(): void
    {
        $writer = new FileWriter(
            new FileSystem(),
            'php://output',
            append: true,
        );

        ob_start();

        try {
            $writer->write("<error>First</error>\n");
            $writer->write("Second\n");
        } finally {
            $output = ob_get_clean();
        }

        $this->assertSame(
            "<error>First</error>\nSecond\n",
            $output,
            'Stream destinations must preserve raw content across writes.',
        );
    }
}
