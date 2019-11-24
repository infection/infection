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

namespace Infection\Tests\Locator;

use Generator;
use Infection\Locator\FileNotFound;
use PHPUnit\Framework\TestCase;

class FileNotFoundTest extends TestCase
{
    /**
     * @dataProvider nonExistentPathsProvider
     *
     * @param string[] $roots
     */
    public function test_file_or_directory_does_not_exist(
        string $file,
        array $roots,
        string $expectedErrorMessage
    ): void {
        $exception = FileNotFound::fromFileName($file, $roots);

        $this->assertSame($expectedErrorMessage, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * @dataProvider multipleNonExistentPathsProvider
     *
     * @param string[] $files
     * @param string[] $roots
     */
    public function test_files_or_directories_does_not_exist(
        array $files,
        array $roots,
        string $expectedErrorMessage
    ): void {
        $exception = FileNotFound::fromFiles($files, $roots);

        $this->assertSame($expectedErrorMessage, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function nonExistentPathsProvider(): Generator
    {
        yield [
            'unknown',
            [],
            'Could not locate the file "unknown".',
        ];

        yield [
            '/unknown',
            [],
            'Could not locate the file "/unknown".',
        ];

        yield [
            'unknown',
            ['root'],
            'Could not locate the file "unknown" in "root".',
        ];

        yield [
            'unknown',
            ['root1', 'root2'],
            'Could not locate the file "unknown" in "root1", "root2".',
        ];
    }

    public function multipleNonExistentPathsProvider(): Generator
    {
        yield [
            [],
            [],
            'Could not locate any files (no file provided).',
        ];

        yield [
            ['unknown'],
            [],
            'Could not locate the files "unknown"',
        ];

        yield [
            ['unknown1', 'unknown2'],
            [],
            'Could not locate the files "unknown1", "unknown2"',
        ];

        yield [
            [],
            ['root'],
            'Could not locate any files (no file provided).',
        ];

        yield [
            [],
            ['root1', 'root2'],
            'Could not locate any files (no file provided).',
        ];

        yield [
            ['unknown'],
            ['root'],
            'Could not locate the files "unknown" in "root"',
        ];

        yield [
            ['unknown1', 'unknown2'],
            ['root'],
            'Could not locate the files "unknown1", "unknown2" in "root"',
        ];

        yield [
            ['unknown'],
            ['root1', 'root2'],
            'Could not locate the files "unknown" in "root1", "root2"',
        ];

        yield [
            ['unknown1', 'unknown2'],
            ['root1', 'root2'],
            'Could not locate the files "unknown1", "unknown2" in "root1", "root2"',
        ];
    }
}
