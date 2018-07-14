<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Coverage;

use Infection\TestFramework\Coverage\CoverageDoesNotExistException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CoverageDoesNotExistExceptionTest extends TestCase
{
    public function test_with()
    {
        $exception = CoverageDoesNotExistException::with(
            'file-index-path',
            'phpunit',
            'tempdir'
        );

        $this->assertInstanceOf(CoverageDoesNotExistException::class, $exception);
        $this->assertSame(
            'Code Coverage does not exist. File file-index-path is not found. Check phpunit version Infection was run with and generated config files inside tempdir. Make sure to either: ' . PHP_EOL .
            '- Enable xdebug and run infection again' . PHP_EOL .
            '- Use phpdbg: phpdbg -qrr infection' . PHP_EOL .
            '- Use --coverage option with path to the existing coverage report' . PHP_EOL .
            '- Use --initial-tests-php-options option with `-d zend_extension=xdebug.so` and/or any extra php parameters', $exception->getMessage()
        );
    }

    public function test_for_junit()
    {
        $exception = CoverageDoesNotExistException::forJunit('junit/file/path');

        $this->assertInstanceOf(CoverageDoesNotExistException::class, $exception);

        $this->assertSame('Coverage report `junit` is not found in junit/file/path', $exception->getMessage());
    }

    public function test_for_file_at_path()
    {
        $exception = CoverageDoesNotExistException::forFileAtPath('file.php', '/path/to/file');

        $this->assertInstanceOf(CoverageDoesNotExistException::class, $exception);

        $this->assertSame('Source file file.php was not found at /path/to/file', $exception->getMessage());
    }
}
