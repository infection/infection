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

namespace Infection\Tests\Finder;

use Infection\Finder\SourceFilesFinder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

final class SourceFilesFinderTest extends TestCase
{
    public function test_it_lists_all_php_files_without_a_filter(): void
    {
        $sourceFilesFinder = new SourceFilesFinder(['tests/Fixtures/Files/Finder'], []);

        $files = $sourceFilesFinder->getSourceFiles();

        $this->assertInstanceOf(Finder::class, $files);
        $this->assertSame(2, $files->count());
    }

    public function test_it_can_filter_one_file_by_a_relative_path(): void
    {
        $sourceFilesFinder = new SourceFilesFinder(['tests/Fixtures/Files/Finder'], []);

        $filter = 'tests/Fixtures/Files/Finder/FirstFile.php';
        $files = $sourceFilesFinder->getSourceFiles($filter);

        $iterator = $files->getIterator();
        $iterator->rewind();
        $firstFile = $iterator->current();

        $this->assertInstanceOf(Finder::class, $files);
        $this->assertSame(1, $files->count());
        $this->assertSame('FirstFile.php', $firstFile->getFilename());
    }

    public function test_it_can_filter_one_file_by_filename(): void
    {
        $sourceFilesFinder = new SourceFilesFinder(['tests/Fixtures/Files/Finder'], []);

        $filter = 'FirstFile.php';
        $files = $sourceFilesFinder->getSourceFiles($filter);

        $iterator = $files->getIterator();
        $iterator->rewind();
        $firstFile = $iterator->current();

        $this->assertInstanceOf(Finder::class, $files);
        $this->assertSame(1, $files->count());
        $this->assertSame('FirstFile.php', $firstFile->getFilename());
    }

    public function test_it_can_filter_a_list_of_files_by_relative_paths(): void
    {
        $sourceFilesFinder = new SourceFilesFinder(['tests/Fixtures/Files/Finder'], []);

        $filter = 'tests/Fixtures/Files/Finder/FirstFile.php,tests/Fixtures/Files/Finder/SecondFile.php';
        $files = $sourceFilesFinder->getSourceFiles($filter);

        $iterator = $files->getIterator();
        $iterator->rewind();
        $firstFile = $iterator->current();
        $iterator->next();
        $secondFile = $iterator->current();

        $this->assertInstanceOf(Finder::class, $files);
        $this->assertSame(2, $files->count());

        $expectedFilenames = ['FirstFile.php', 'SecondFile.php'];

        foreach ([$firstFile, $secondFile] as $file) {
            $this->assertTrue(\in_array($file->getFilename(), $expectedFilenames, true));
        }
    }

    /**
     * IE: --filter=1,,2,3,
     */
    public function test_extra_commas_in_filters(): void
    {
        $sourceFilesFinder = new SourceFilesFinder(['tests/Fixtures/Files/Finder'], []);

        $filter = 'tests/Fixtures/Files/Finder/FirstFile.php,,tests/Fixtures/Files/Finder/SecondFile.php,';
        $files = $sourceFilesFinder->getSourceFiles($filter);

        $iterator = $files->getIterator();
        $iterator->rewind();
        $firstFile = $iterator->current();
        $iterator->next();
        $secondFile = $iterator->current();

        $this->assertInstanceOf(Finder::class, $files);
        $this->assertSame(2, $files->count());

        $expectedFilenames = ['FirstFile.php', 'SecondFile.php'];

        foreach ([$firstFile, $secondFile] as $file) {
            $this->assertTrue(\in_array($file->getFilename(), $expectedFilenames, true));
        }
    }

    public function test_it_can_filter_a_list_of_files_by_filename(): void
    {
        $sourceFilesFinder = new SourceFilesFinder(['tests/Fixtures/Files/Finder'], []);

        $filter = 'FirstFile.php,SecondFile.php';
        $files = $sourceFilesFinder->getSourceFiles($filter);

        $iterator = $files->getIterator();
        $iterator->rewind();
        $firstFile = $iterator->current();
        $iterator->next();
        $secondFile = $iterator->current();

        $this->assertInstanceOf(Finder::class, $files);
        $this->assertSame(2, $files->count());

        $expectedFilenames = ['FirstFile.php', 'SecondFile.php'];

        foreach ([$firstFile, $secondFile] as $file) {
            $this->assertTrue(\in_array($file->getFilename(), $expectedFilenames, true));
        }
    }

    public function test_it_can_filter_to_an_empty_result(): void
    {
        $sourceFilesFinder = new SourceFilesFinder(['tests/Fixtures/Files/Finder'], []);

        $filter = 'ThisFileDoesNotExist.php';
        $files = $sourceFilesFinder->getSourceFiles($filter);

        $this->assertInstanceOf(Finder::class, $files);
        $this->assertSame(0, $files->count());
    }

    public function test_it_can_exclude_a_directory(): void
    {
        $sourceFilesFinder = new SourceFilesFinder(['tests/Fixtures/Files/ExcludeFinder'], ['Folder2']);

        $files = $sourceFilesFinder->getSourceFiles();

        $this->assertInstanceOf(Finder::class, $files);
        $this->assertSame(1, $files->count());

        foreach ($files as $file) {
            $this->assertSame('File.php', $file->getFilename());
        }
    }
}
