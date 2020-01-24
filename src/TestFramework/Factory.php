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

namespace Infection\TestFramework;

use function implode;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Configuration\Configuration;
use Infection\FileSystem\Finder\TestFrameworkFinder;
use Infection\TestFramework\Codeception\CodeceptionAdapterFactory;
use Infection\TestFramework\Config\TestFrameworkConfigLocatorInterface;
use Infection\TestFramework\PhpSpec\Adapter\PhpSpecAdapterFactory;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapterFactory;
use InvalidArgumentException;
use function Safe\sprintf;

/**
 * @internal
 */
final class Factory
{
    private $tmpDir;
    private $configLocator;
    private $projectDir;
    private $jUnitFilePath;
    private $infectionConfig;

    public function __construct(
        string $tmpDir,
        string $projectDir,
        TestFrameworkConfigLocatorInterface $configLocator,
        string $jUnitFilePath,
        Configuration $infectionConfig
    ) {
        $this->tmpDir = $tmpDir;
        $this->configLocator = $configLocator;
        $this->projectDir = $projectDir;
        $this->jUnitFilePath = $jUnitFilePath;
        $this->infectionConfig = $infectionConfig;
    }

    public function create(string $adapterName, bool $skipCoverage): TestFrameworkAdapter
    {
        if ($adapterName === TestFrameworkTypes::PHPUNIT) {
            $phpUnitConfigPath = $this->configLocator->locate(TestFrameworkTypes::PHPUNIT);

            return PhpUnitAdapterFactory::create(
                (new TestFrameworkFinder(
                    TestFrameworkTypes::PHPUNIT,
                    (string) $this->infectionConfig->getPhpUnit()->getCustomPath()
                ))->find(),
                $this->tmpDir,
                $phpUnitConfigPath,
                (string) $this->infectionConfig->getPhpUnit()->getConfigDir(),
                $this->jUnitFilePath,
                $this->projectDir,
                $this->infectionConfig->getSourceDirectories(),
                $skipCoverage
            );
        }

        if ($adapterName === TestFrameworkTypes::PHPSPEC) {
            $phpSpecConfigPath = $this->configLocator->locate(TestFrameworkTypes::PHPSPEC);

            return PhpSpecAdapterFactory::create(
                (new TestFrameworkFinder(TestFrameworkTypes::PHPSPEC))->find(),
                $this->tmpDir,
                $phpSpecConfigPath,
                null,
                $this->jUnitFilePath,
                $this->projectDir,
                $this->infectionConfig->getSourceDirectories(),
                $skipCoverage
            );
        }

        if ($adapterName === TestFrameworkTypes::CODECEPTION) {
            $codeceptionConfigPath = $this->configLocator->locate(TestFrameworkTypes::CODECEPTION);

            return CodeceptionAdapterFactory::create(
                (new TestFrameworkFinder('codecept'))->find(),
                $this->tmpDir,
                $codeceptionConfigPath,
                null,
                $this->jUnitFilePath,
                $this->projectDir,
                $this->infectionConfig->getSourceDirectories(),
                $skipCoverage
            );
        }

        throw new InvalidArgumentException(sprintf(
            'Invalid name of test framework "%s". Available names are: %s',
            $adapterName,
            implode(', ', [TestFrameworkTypes::PHPUNIT, TestFrameworkTypes::PHPSPEC, TestFrameworkTypes::CODECEPTION])
        ));
    }
}
