<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\Tests\Filesystem\Exception;

use Infection\Filesystem\Exception\IOException;
use PHPUnit\Framework\TestCase;

class IOExceptionTest extends TestCase
{
    public function test_it_is_a_runtime_exception()
    {
        $exception = new IOException('error');
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function test_directory_not_writable_formats_correctly()
    {
        $dir = 'fake/file/path';
        $exception = IOException::directoryNotWritable($dir);

        $message = sprintf(
            'Unable to write to the "%s" directory.',
            $dir
        );

        $this->assertSame($message, $exception->getMessage());
        $this->assertInstanceOf(IOException::class, $exception);
    }

    public function test_unable_to_create_formats_correctly()
    {
        $dir = 'fake/file/path';
        $exception = IOException::unableToCreate($dir);

        $message = sprintf('Failed to create "%s"', $dir);

        $this->assertSame($message, $exception->getMessage());
        $this->assertInstanceOf(IOException::class, $exception);
    }

    public function test_unable_to_create_formats_correctly_with_error_message()
    {
        $dir = 'fake/file/path';
        $message = 'Path is in another dimension.';
        $exception = IOException::unableToCreate($dir, $message);

        $message = sprintf(
            'Failed to create "%s": %s',
            $dir,
            $message
        );

        $this->assertSame($message, $exception->getMessage());
        $this->assertInstanceOf(IOException::class, $exception);
    }

    public function test_unable_to_write_to_file_formats_correctly()
    {
        $filename = 'hello_world.php';
        $message = sprintf(
            'Failed to write file "%s".',
            $filename
        );

        $exception = IOException::unableToWriteToFile($filename);

        $this->assertSame($message, $exception->getMessage());
        $this->assertInstanceOf(IOException::class, $exception);
    }
}
