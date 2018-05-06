<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Finder;

use Infection\Finder\SourceFilesFinder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

/**
 * @internal
 */
final class SourceFilesFinderTest extends TestCase
{
    public function test_it_lists_all_php_files_without_a_filter()
    {
        $sourceFilesFinder = new SourceFilesFinder(['tests/Fixtures/Files/Finder'], []);

        $files = $sourceFilesFinder->getSourceFiles();

        $this->assertInstanceOf(Finder::class, $files);
        $this->assertSame(2, $files->count());
    }

    public function test_it_can_filter_one_file_by_a_relative_path()
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

    public function test_it_can_filter_one_file_by_filename()
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

    public function test_it_can_filter_a_list_of_files_by_relative_paths()
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

    public function test_it_can_filter_a_list_of_files_by_filename()
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

    public function test_it_can_filter_to_an_empty_result()
    {
        $sourceFilesFinder = new SourceFilesFinder(['tests/Fixtures/Files/Finder'], []);

        $filter = 'ThisFileDoesNotExist.php';
        $files = $sourceFilesFinder->getSourceFiles($filter);

        $this->assertInstanceOf(Finder::class, $files);
        $this->assertSame(0, $files->count());
    }

    public function test_it_can_exclude_a_directory()
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
