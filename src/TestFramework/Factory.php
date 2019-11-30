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

use Infection\Configuration\Configuration;
use Infection\Finder\TestFrameworkFinder;
use Infection\TestFramework\Codeception\Adapter\CodeceptionAdapter;
use Infection\TestFramework\Config\TestFrameworkConfigLocatorInterface;
use Infection\TestFramework\Coverage\JUnitTestCaseSorter;
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
use InvalidArgumentException;
use LogicException;
use function Safe\file_get_contents;
use function Safe\sprintf;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

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
     * @var Configuration
     */
    private $infectionConfig;

    /**
     * @var VersionParser
     */
    private $versionParser;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var CommandLineBuilder
     */
    private $commandLineBuilder;

    public function __construct(
        string $tmpDir,
        string $projectDir,
        TestFrameworkConfigLocatorInterface $configLocator,
        XmlConfigurationHelper $xmlConfigurationHelper,
        string $jUnitFilePath,
        Configuration $infectionConfig,
        VersionParser $versionParser,
        Filesystem $filesystem,
        CommandLineBuilder $commandLineBuilder
    ) {
        $this->tmpDir = $tmpDir;
        $this->configLocator = $configLocator;
        $this->xmlConfigurationHelper = $xmlConfigurationHelper;
        $this->projectDir = $projectDir;
        $this->jUnitFilePath = $jUnitFilePath;
        $this->infectionConfig = $infectionConfig;
        $this->versionParser = $versionParser;
        $this->filesystem = $filesystem;
        $this->commandLineBuilder = $commandLineBuilder;
    }

    public function create(string $adapterName, bool $skipCoverage): TestFrameworkAdapter
    {
        if ($adapterName === TestFrameworkTypes::PHPUNIT) {
            $phpUnitConfigPath = $this->configLocator->locate(TestFrameworkTypes::PHPUNIT);
            $phpUnitConfigContent = file_get_contents($phpUnitConfigPath);

            return new PhpUnitAdapter(
                (new TestFrameworkFinder(
                    TestFrameworkTypes::PHPUNIT,
                    (string) $this->infectionConfig->getPhpUnit()->getCustomPath()
                ))->find(),
                new InitialConfigBuilder(
                    $this->tmpDir,
                    $phpUnitConfigContent,
                    $this->xmlConfigurationHelper,
                    $this->jUnitFilePath,
                    $this->infectionConfig->getSource()->getDirectories(),
                    $skipCoverage
                ),
                new MutationConfigBuilder($this->tmpDir, $phpUnitConfigContent, $this->xmlConfigurationHelper, $this->projectDir, new JUnitTestCaseSorter()),
                new ArgumentsAndOptionsBuilder(),
                $this->versionParser,
                $this->commandLineBuilder
            );
        }

        if ($adapterName === TestFrameworkTypes::PHPSPEC) {
            $phpSpecConfigPath = $this->configLocator->locate(TestFrameworkTypes::PHPSPEC);

            return new PhpSpecAdapter(
                (new TestFrameworkFinder(TestFrameworkTypes::PHPSPEC))->find(),
                new PhpSpecInitialConfigBuilder($this->tmpDir, $phpSpecConfigPath, $skipCoverage),
                new PhpSpecMutationConfigBuilder($this->tmpDir, $phpSpecConfigPath, $this->projectDir),
                new PhpSpecArgumentsAndOptionsBuilder(),
                $this->versionParser,
                $this->commandLineBuilder
            );
        }

        if ($adapterName === TestFrameworkTypes::CODECEPTION) {
            $this->ensureCodeceptionVersionIsSupported();
            $codeceptionConfigPath = $this->configLocator->locate(TestFrameworkTypes::CODECEPTION);
            $codeceptionConfigContentParsed = $this->parseYaml($codeceptionConfigPath);

            return new CodeceptionAdapter(
                (new TestFrameworkFinder(CodeceptionAdapter::EXECUTABLE))->find(),
                $this->commandLineBuilder,
                $this->versionParser,
                new JUnitTestCaseSorter(),
                $this->filesystem,
                $this->jUnitFilePath,
                $this->tmpDir,
                $this->projectDir,
                $codeceptionConfigContentParsed,
                $this->infectionConfig->getSource()->getDirectories()
            );
        }

        throw new InvalidArgumentException(sprintf(
            'Invalid name of test framework "%s". Available names are: %s',
            $adapterName,
            implode(', ', [TestFrameworkTypes::PHPUNIT, TestFrameworkTypes::PHPSPEC])
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function parseYaml(string $codeceptionConfigPath): array
    {
        $codeceptionConfigContent = file_get_contents($codeceptionConfigPath);

        try {
            $codeceptionConfigContentParsed = Yaml::parse($codeceptionConfigContent);
        } catch (ParseException $e) {
            throw TestFrameworkConfigParseException::fromPath($codeceptionConfigPath, $e);
        }

        return $codeceptionConfigContentParsed;
    }

    private function ensureCodeceptionVersionIsSupported(): void
    {
        if (!class_exists('\Codeception\Codecept')) {
            return;
        }

        if (version_compare(\Codeception\Codecept::VERSION, '3.1.1', '<')) {
            throw new LogicException(
                sprintf(
                    'Current Codeception version "%s" is not supported by Infection. Please use >=3.1.1',
                    \Codeception\Codecept::VERSION
                )
            );
        }
    }
}
