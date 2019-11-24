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

namespace Infection\Tests\TestFramework\Coverage;

use Infection\TestFramework\Coverage\CoverageDoesNotExistException;
use PHPUnit\Framework\TestCase;

class CoverageDoesNotExistExceptionTest extends TestCase
{
    public function test_with(): void
    {
        $exception = CoverageDoesNotExistException::with(
            'file-index-path',
            'phpunit',
            'tempdir'
        );

        $this->assertInstanceOf(CoverageDoesNotExistException::class, $exception);
        $this->assertSame(
            'Code Coverage does not exist. File file-index-path is not found. Check phpunit version Infection was run with and generated config files inside tempdir. Make sure to either: ' . "\n" .
            '- Enable xdebug and run infection again' . "\n" .
            '- Use phpdbg: phpdbg -qrr infection' . "\n" .
            '- Use --coverage option with path to the existing coverage report' . "\n" .
            '- Use --initial-tests-php-options option with `-d zend_extension=xdebug.so` and/or any extra php parameters', $exception->getMessage()
        );
    }

    public function test_for_junit(): void
    {
        $exception = CoverageDoesNotExistException::forJunit('junit/file/path');

        $this->assertInstanceOf(CoverageDoesNotExistException::class, $exception);

        $this->assertSame('Coverage report `junit` is not found in junit/file/path', $exception->getMessage());
    }

    public function test_for_file_at_path(): void
    {
        $exception = CoverageDoesNotExistException::forFileAtPath('file.php', '/path/to/file');

        $this->assertInstanceOf(CoverageDoesNotExistException::class, $exception);

        $this->assertSame('Source file file.php was not found at /path/to/file', $exception->getMessage());
    }

    public function test_for_correctly_escaped_output_of_subprocesses(): void
    {
        $exception = CoverageDoesNotExistException::with('foo.xml', 'bar', '/baz', 'string with a single % and placeholders %s %d %i.');

        $this->assertInstanceOf(CoverageDoesNotExistException::class, $exception);

        $this->assertStringStartsWith('Code Coverage does not exist. File foo.xml is not found. Check bar version Infection was run with and generated config files inside /baz.string with a single % and placeholders %s %d %i.', $exception->getMessage());
    }

    public function test_log_missed_debugger_or_coverage_option(): void
    {
        $message = 'Neither phpdbg or xdebug has been found. One of those is required by Infection in order to generate coverage data. Either:' . "\n" .
            '- Enable xdebug and run infection again' . "\n" .
            '- Use phpdbg: phpdbg -qrr infection' . "\n" .
            '- Use --coverage option with path to the existing coverage report' . "\n" .
            '- Use --initial-tests-php-options option with `-d zend_extension=xdebug.so` and/or any extra php parameters';

        $this->assertSame($message, CoverageDoesNotExistException::unableToGenerate()->getMessage());
    }
}
