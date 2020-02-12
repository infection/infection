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

namespace Infection\Tests\Config\ValueProvider;

use const DIRECTORY_SEPARATOR;
use Infection\Config\ConsoleHelper;
use Infection\Config\ValueProvider\ExcludeDirsProvider;
use function microtime;
use function random_int;
use function Safe\mkdir;
use Symfony\Component\Filesystem\Filesystem;
use function sys_get_temp_dir;

/**
 * @group integration Requires some I/O operations
 */
final class ExcludeDirsProviderTest extends AbstractBaseProviderTest
{
    /**
     * @var string
     */
    private $workspace;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var ExcludeDirsProvider
     */
    private $provider;

    protected function setUp(): void
    {
        $this->workspace = sys_get_temp_dir() . '/exclude' . microtime(true) . random_int(100, 999);
        mkdir($this->workspace, 0777, true);

        $this->fileSystem = new Filesystem();

        $this->provider = new ExcludeDirsProvider(
            $this->createMock(ConsoleHelper::class),
            $this->getQuestionHelper(),
            $this->fileSystem
        );
    }

    protected function tearDown(): void
    {
        $this->fileSystem->remove($this->workspace);
    }

    /**
     * @dataProvider excludeDirsProvider
     */
    public function test_it_contains_vendors_when_sources_contains_current_dir(string $excludedRootDir, array $dirsInCurrentFolder): void
    {
        $excludedDirs = $this->provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("\n")),
            $this->createOutputInterface(),
            $dirsInCurrentFolder,
            ['.']
        );

        $this->assertContains($excludedRootDir, $excludedDirs);
    }

    public function test_it_validates_dirs(): void
    {
        if (!$this->hasSttyAvailable()) {
            $this->markTestSkipped('Stty is not available');
        }

        $excludeDirs = $this->provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("abc\n")),
            $this->createOutputInterface(),
            ['src'],
            ['src']
        );

        $this->assertCount(0, $excludeDirs);
    }

    public function test_passes_when_correct_dir_typed(): void
    {
        if (!$this->hasSttyAvailable()) {
            $this->markTestSkipped('Stty is not available');
        }

        $dir1 = $this->workspace . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR;
        $dir2 = $this->workspace . DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR;

        mkdir($dir1);
        mkdir($dir2);

        $excludeDirs = $this->provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("foo\n")),
            $this->createOutputInterface(),
            ['src'],
            [$this->workspace]
        );

        $this->assertContains('foo', $excludeDirs);
    }

    public function test_returns_an_array_with_incremented_keys_started_from_zero(): void
    {
        if (!$this->hasSttyAvailable()) {
            $this->markTestSkipped('Stty is not available');
        }

        $dirA = $this->workspace . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR;
        $dirB = $this->workspace . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR;
        $dirC = $this->workspace . DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR;

        mkdir($dirA);
        mkdir($dirB);
        mkdir($dirC);

        $excludeDirs = $this->provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("$dirA\n$dirA\n$dirC\n")),
            $this->createOutputInterface(),
            [$dirA, $dirB, $dirC],
            ['.']
        );

        $this->assertSame([0 => $dirA, 1 => $dirC], $excludeDirs);
    }

    public function excludeDirsProvider()
    {
        return array_map(
            static function (string $excludedRootDir) {
                return [$excludedRootDir, [$excludedRootDir, 'src']];
            },
            ExcludeDirsProvider::EXCLUDED_ROOT_DIRS
        );
    }
}
