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

/*
 * This file is part of the box project.
 *
 * (c) Kevin Herrera <kevin@herrera.io>
 *     Théo Fidry <theo.fidry@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use function Infection\Tests\make_tmp_dir;
use function Infection\Tests\normalizePath;
use PHPUnit\Framework\TestCase;
use function Safe\getcwd;
use function Safe\realpath;
use Symfony\Component\Filesystem\Filesystem;
use function sys_get_temp_dir;

/**
 * @private
 */
abstract class FileSystemTestCase extends TestCase
{
    private const TMP_DIR_NAME = 'infection-test';

    /**
     * @var string
     */
    protected $cwd;

    /**
     * @var string
     */
    protected $tmp;

    public static function tearDownAfterClass(): void
    {
        // Cleans up whatever was there before. Indeed upon failure PHPUnit fails to trigger the
        // `tearDown()` method and as a result some temporary files may still remain.
        self::removeTmpDir();
    }

    protected function setUp(): void
    {
        // Cleans up whatever was there before. Indeed upon failure PHPUnit fails to trigger the
        // `tearDown()` method and as a result some temporary files may still remain.
        self::removeTmpDir();

        $this->cwd = getcwd();
        $this->tmp = make_tmp_dir(self::TMP_DIR_NAME, self::class);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->tmp);
    }

    final protected static function removeTmpDir(): void
    {
        (new Filesystem())->remove(
            normalizePath(
                realpath(sys_get_temp_dir()) . '/' . self::TMP_DIR_NAME
            )
        );
    }
}
