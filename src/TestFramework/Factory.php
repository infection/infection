<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2019, Maks Rafalko
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

use Infection\Config\InfectionConfig;
use Infection\EventDispatcher\EventDispatcher;
use Infection\Events\LoadTestFramework;
use Infection\Finder\TestFrameworkFinder;
use Infection\TestFramework\Config\TestFrameworkConfigLocatorInterface;
use Infection\TestFramework\PhpSpec\Adapter\PhpSpecAdapter;
use Infection\TestFramework\PhpSpec\CommandLine\ArgumentsAndOptionsBuilder as PhpSpecArgumentsAndOptionsBuilder;
use Infection\TestFramework\PhpSpec\Config\Builder\InitialConfigBuilder as PhpSpecInitialConfigBuilder;
use Infection\TestFramework\PhpSpec\Config\Builder\MutationConfigBuilder as PhpSpecMutationConfigBuilder;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use Infection\TestFramework\PhpUnit\CommandLine\ArgumentsAndOptionsBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;
use Infection\Utils\VersionParser;

/**
 * @internal
 */
final class Factory
{
    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @var XmlConfigurationHelper
     */
    private $xmlConfigurationHelper;

    /**
     * @var TestFrameworkConfigLocatorInterface
     */
    private $configLocator;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @var string
     */
    private $jUnitFilePath;

    /**
     * @var InfectionConfig
     */
    private $infectionConfig;

    /**
     * @var VersionParser
     */
    private $versionParser;

    /** @var EventDispatcher */
    private $eventDispatcher;

    public function __construct(
        string $tmpDir,
        string $projectDir,
        TestFrameworkConfigLocatorInterface $configLocator,
        XmlConfigurationHelper $xmlConfigurationHelper,
        string $jUnitFilePath,
        InfectionConfig $infectionConfig,
        VersionParser $versionParser,
        EventDispatcher $eventDispatcher
    ) {
        $this->tmpDir = $tmpDir;
        $this->configLocator = $configLocator;
        $this->xmlConfigurationHelper = $xmlConfigurationHelper;
        $this->projectDir = $projectDir;
        $this->jUnitFilePath = $jUnitFilePath;
        $this->infectionConfig = $infectionConfig;
        $this->versionParser = $versionParser;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(string $adapterName, bool $skipCoverage): AbstractTestFrameworkAdapter
    {
        if ($adapterName === TestFrameworkTypes::PHPUNIT) {
            $phpUnitConfigPath = $this->configLocator->locate(TestFrameworkTypes::PHPUNIT);
            $phpUnitConfigContent = file_get_contents($phpUnitConfigPath);
            \assert(\is_string($phpUnitConfigContent));

            return new PhpUnitAdapter(
                new TestFrameworkFinder(TestFrameworkTypes::PHPUNIT, $this->infectionConfig->getPhpUnitCustomPath()),
                new InitialConfigBuilder(
                    $this->tmpDir,
                    $phpUnitConfigContent,
                    $this->xmlConfigurationHelper,
                    $this->jUnitFilePath,
                    $this->infectionConfig->getSourceDirs(),
                    $skipCoverage
                ),
                new MutationConfigBuilder($this->tmpDir, $phpUnitConfigContent, $this->xmlConfigurationHelper, $this->projectDir),
                new ArgumentsAndOptionsBuilder(),
                $this->versionParser
            );
        }

        if ($adapterName === TestFrameworkTypes::PHPSPEC) {
            $phpSpecConfigPath = $this->configLocator->locate(TestFrameworkTypes::PHPSPEC);

            return new PhpSpecAdapter(
                new TestFrameworkFinder(TestFrameworkTypes::PHPSPEC),
                new PhpSpecInitialConfigBuilder($this->tmpDir, $phpSpecConfigPath, $skipCoverage),
                new PhpSpecMutationConfigBuilder($this->tmpDir, $phpSpecConfigPath, $this->projectDir),
                new PhpSpecArgumentsAndOptionsBuilder(),
                $this->versionParser
            );
        }

        $event = new LoadTestFramework(
            $adapterName,
            [
                'skipCoverage' => $skipCoverage,
                'tmpDir' => $this->tmpDir,
                'configLocator' => $this->configLocator,
                'xmlConfigurationHelper' => $this->xmlConfigurationHelper,
                'projectDir' => $this->projectDir,
                'jUnitFilePath' => $this->jUnitFilePath,
                'infectionConfig' => $this->infectionConfig,
                'versionParser' => $this->versionParser,
            ]
        );

        $this->eventDispatcher->dispatch($event);

        if ($event->getAdapter()) {
            return $event->getAdapter();
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Invalid name of test framework. Available names are: %s',
                implode(', ', TestFrameworkTypes::getTypes())
            )
        );
    }
}
