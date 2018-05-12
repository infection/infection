<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Finder\Exception;

use Infection\Finder\Exception\LocatorException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class LocatorExceptionTest extends TestCase
{
    public function test_file_or_directory_does_not_exist()
    {
        $exception = LocatorException::fileOrDirectoryDoesNotExist('file');

        $this->assertInstanceOf(LocatorException::class, $exception);
        $this->assertSame(
            'The file/directory "file" does not exist.',
            $exception->getMessage()
        );
    }

    public function test_files_or_directories_do_not_exist()
    {
        $exception = LocatorException::filesOrDirectoriesDoNotExist('file', ['foo/', 'bar/']);

        $this->assertInstanceOf(LocatorException::class, $exception);
        $this->assertSame(
            'The file/folder "file" does not exist (in: foo/, bar/).',
            $exception->getMessage()
        );
    }

    public function test_multiple_files_do_not_exist()
    {
        $exception = LocatorException::multipleFilesDoNotExist('foo/bar/', ['file1', 'file2']);

        $this->assertInstanceOf(LocatorException::class, $exception);
        $this->assertSame(
            'The path foo/bar/ does not contain any of the requested files: file1, file2',
            $exception->getMessage()
        );
    }

    public function test_files_not_found()
    {
        $exception = LocatorException::filesNotFound();

        $this->assertInstanceOf(LocatorException::class, $exception);
        $this->assertSame(
            'Files are not found',
            $exception->getMessage()
        );
    }
}
