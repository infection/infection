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

namespace Infection\Tests\Finder\Exception;

use Infection\Finder\Exception\FinderException;
use PHPUnit\Framework\TestCase;

final class FinderExceptionTest extends TestCase
{
    public function test_composer_not_found_exception(): void
    {
        $exception = FinderException::composerNotFound();

        $this->assertInstanceOf(FinderException::class, $exception);
        $this->assertStringContainsString(
            'Unable to locate a Composer executable on local system',
            $exception->getMessage()
        );
    }

    public function test_php_executable_not_found(): void
    {
        $exception = FinderException::phpExecutableNotFound();

        $this->assertInstanceOf(FinderException::class, $exception);
        $this->assertStringContainsString(
            'Unable to locate the PHP executable on the local system',
            $exception->getMessage()
        );
    }

    public function test_test_framework_not_found(): void
    {
        $exception = FinderException::testFrameworkNotFound('framework');

        $this->assertInstanceOf(FinderException::class, $exception);
        $this->assertStringContainsString(
            'Unable to locate a framework executable on local system.',
            $exception->getMessage()
        );
        $this->assertStringContainsString(
            'Ensure that framework is installed and available.',
            $exception->getMessage()
        );
    }

    public function test_test_custom_path_does_not_exsist(): void
    {
        $exception = FinderException::testCustomPathDoesNotExist('framework', 'foo/bar/abc');

        $this->assertInstanceOf(FinderException::class, $exception);
        $this->assertStringContainsString(
            'The custom path to framework was set as "foo/bar/abc"',
            $exception->getMessage()
        );
    }
}
