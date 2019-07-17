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

namespace Infection\Tests\Config;

use Infection\Config\ConfigCreatorFacade;
use Infection\Config\InfectionConfig;
use Infection\Locator\FileNotFound;
use Infection\Locator\Locator;
use Infection\Utils\TmpDirectoryCreator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class ConfigCreatorFacadeTest extends TestCase
{
    /**
     * @var ConfigCreatorFacade
     */
    private $creatorFacade;

    /**
     * @var Locator&MockObject
     */
    private $locator;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $workspace;

    /**
     * @var string
     */
    private $tmpDir;

    protected function setUp(): void
    {
        $this->locator = $this->createMock(Locator::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);

        $this->creatorFacade = new ConfigCreatorFacade($this->locator, $this->filesystemMock);

        $this->filesystem = new Filesystem();
        $this->workspace = sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'infection-test' . \microtime(true) . \random_int(100, 999);
        $this->tmpDir = (new TmpDirectoryCreator($this->filesystem))->createAndGet($this->workspace);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->workspace);
    }

    public function test_it_creates_config_class_successfully(): void
    {
        $json = '{"timeout": 25, "source": {"directories": ["src"]}}';

        $customConfigPath = $this->tmpDir . '/file.json';

        $this->locator
            ->method('locateOneOf')
            ->willReturn($customConfigPath);

        $this->filesystem->dumpFile($customConfigPath, $json);

        $infectionConfig = $this->creatorFacade->createConfig($customConfigPath);

        self::assertSame($infectionConfig->getProcessTimeout(), 25);
    }

    public function test_it_creates_empty_config_class_successfully(): void
    {
        $customConfigPath = $this->tmpDir . '/file.json';

        $this->filesystem->dumpFile($customConfigPath, '{"timeout": 15, "source": {"directories": ["src"]}}');

        $this->locator
            ->method('locateOneOf')
            ->willReturn($customConfigPath);

        $infectionConfig = $this->creatorFacade->createConfig(null);

        self::assertSame($infectionConfig->getProcessTimeout(), 15);
    }

    public function test_it_creates_an_empty_config_with_default_parameters_when_locator_throws_exception(): void
    {
        $this->locator
            ->method('locateOneOf')
            ->will($this->throwException(new FileNotFound()));

        $infectionConfig = $this->creatorFacade->createConfig(null);

        self::assertSame($infectionConfig->getProcessTimeout(), InfectionConfig::PROCESS_TIMEOUT_SECONDS);
    }
}
