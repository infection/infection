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

namespace Infection\Tests\Utils;

use Infection\Utils\TmpDirectoryCreator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use function Infection\Tests\make_tmp_dir;
use function Infection\Tests\normalizePath;

/**
 * @group integration
 * @coversNothing
 */
final class TmpDirectoryCreatorIntegrationTest extends TestCase
{
    /**
     * @var TmpDirectoryCreator
     */
    private $tmpDirCreator;

    /**
     * @var string
     */
    private $tmp;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    protected function setUp(): void
    {
        $this->fileSystem = new Filesystem();

        $this->tmpDirCreator = new TmpDirectoryCreator($this->fileSystem);

        // Cleans up whatever was there before. Indeed upon failure PHPUnit fails to trigger the `tearDown()` method
        // and as a result some temporary files may still remain.
        $this->fileSystem->remove(normalizePath(realpath(sys_get_temp_dir())).'/infection-test');

        $this->tmp = make_tmp_dir('infection-test', self::class);
    }

    protected function tearDown(): void
    {
        $this->fileSystem->remove($this->tmp);
    }

    public function test_it_creates_a_tmp_dir_and_returns_its_path(): void
    {
        $expectedTmpDir = $this->tmp.'/infection';

        $this->assertDirectoryNotExists($expectedTmpDir);

        $actualTmpDir = $this->tmpDirCreator->createAndGet($this->tmp);

        $this->assertSame($expectedTmpDir, $actualTmpDir);

        $this->assertDirectoryExists($actualTmpDir);
    }
}
