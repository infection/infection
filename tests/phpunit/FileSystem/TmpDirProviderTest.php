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

namespace Infection\Tests\FileSystem;

use Infection\FileSystem\TmpDirProvider;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class TmpDirProviderTest extends TestCase
{
    /**
     * @var TmpDirProvider
     */
    private $tmpDirProvider;

    protected function setUp(): void
    {
        $this->tmpDirProvider = new TmpDirProvider();
    }

    /**
     * @dataProvider tmpDirProvider
     */
    public function test_it_provides_a_tmp_dir_path(
        string $tmpDir,
        string $expectedTmpDir
    ): void {
        $actualTmpDir = $this->tmpDirProvider->providePath($tmpDir);

        $this->assertSame($expectedTmpDir, $actualTmpDir);
    }

    public function test_it_is_deterministic(): void
    {
        $tmpDir = '/path/to/tmp';
        $expectedTmpDir = '/path/to/tmp/infection';

        $this->assertSame(
            $expectedTmpDir,
            $this->tmpDirProvider->providePath($tmpDir)
        );
        $this->assertSame(
            $expectedTmpDir,
            $this->tmpDirProvider->providePath($tmpDir)
        );
    }

    public function test_it_provides_a_different_path_for_different_base_tmp_dir(): void
    {
        $this->assertSame(
            '/path/to/tmp/infection',
            $this->tmpDirProvider->providePath('/path/to/tmp')
        );

        $this->assertSame(
            '/path/to/another-tmp/infection',
            $this->tmpDirProvider->providePath('/path/to/another-tmp')
        );
    }

    /**
     * @dataProvider invalidTmpDirProvider
     */
    public function test_the_tmp_dir_given_must_be_an_absolute_path(
        string $tmpDir,
        string $expectedErrorMessage
    ): void {
        try {
            $this->tmpDirProvider->providePath($tmpDir);

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                $expectedErrorMessage,
                $exception->getMessage()
            );
        }
    }

    public function tmpDirProvider(): iterable
    {
        yield 'root dir path' => [
            '/',
            '/infection',
        ];

        yield 'nominal' => [
            '/path/to/tmp',
            '/path/to/tmp/infection',
        ];

        yield 'path with ending slash' => [
            '/path/to/tmp/',
            '/path/to/tmp/infection',
        ];
    }

    public function invalidTmpDirProvider(): iterable
    {
        yield 'empty dir path' => [
            '',
            'Expected the temporary directory passed to be an absolute path. Got ""',
        ];

        yield 'relative path' => [
            'relative/path/to/tmp',
            'Expected the temporary directory passed to be an absolute path. Got "relative/path/to/tmp"',
        ];
    }
}
