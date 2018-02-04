<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\TestFramework;

use Infection\Config\InfectionConfig;
use Infection\Finder\TestFrameworkExecutableFinder;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use Infection\TestFramework\PhpSpec\Adapter\PhpSpecAdapter;
use Infection\TestFramework\PhpSpec\Config\Builder\InitialConfigBuilder as PhpSpecInitialConfigBuilder;
use Infection\TestFramework\PhpSpec\Config\Builder\MutationConfigBuilder as PhpSpecMutationConfigBuilder;
use Infection\TestFramework\PhpSpec\CommandLine\ArgumentsAndOptionsBuilder as PhpSpecArgumentsAndOptionsBuilder;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use Infection\TestFramework\PhpUnit\CommandLine\ArgumentsAndOptionsBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;
use Infection\Utils\VersionParser;

final class Factory
{
    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var XmlConfigurationHelper
     */
    private $xmlConfigurationHelper;

    /**
     * @var TestFrameworkConfigLocator
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

    public function __construct(
        string $tempDir,
        string $projectDir,
        TestFrameworkConfigLocator $configLocator,
        XmlConfigurationHelper $xmlConfigurationHelper,
        string $jUnitFilePath,
        InfectionConfig $infectionConfig,
        VersionParser $versionParser
    ) {
        $this->tempDir = $tempDir;
        $this->configLocator = $configLocator;
        $this->xmlConfigurationHelper = $xmlConfigurationHelper;
        $this->projectDir = $projectDir;
        $this->jUnitFilePath = $jUnitFilePath;
        $this->infectionConfig = $infectionConfig;
        $this->versionParser = $versionParser;
    }

    public function create(string $adapterName): AbstractTestFrameworkAdapter
    {
        if ($adapterName === TestFrameworkTypes::PHPUNIT) {
            $phpUnitConfigPath = $this->configLocator->locate(TestFrameworkTypes::PHPUNIT);
            $phpUnitConfigContent = file_get_contents($phpUnitConfigPath);

            return new PhpUnitAdapter(
                new TestFrameworkExecutableFinder(TestFrameworkTypes::PHPUNIT, $this->infectionConfig->getPhpUnitCustomPath()),
                new InitialConfigBuilder($this->tempDir, $phpUnitConfigContent, $this->xmlConfigurationHelper, $this->jUnitFilePath, $this->infectionConfig->getSourceDirs()),
                new MutationConfigBuilder($this->tempDir, $phpUnitConfigContent, $this->xmlConfigurationHelper, $this->projectDir),
                new ArgumentsAndOptionsBuilder(),
                $this->versionParser
            );
        }

        if ($adapterName === TestFrameworkTypes::PHPSPEC) {
            $phpSpecConfigPath = $this->configLocator->locate(TestFrameworkTypes::PHPSPEC);

            return new PhpSpecAdapter(
                new TestFrameworkExecutableFinder(TestFrameworkTypes::PHPSPEC),
                new PhpSpecInitialConfigBuilder($this->tempDir, $phpSpecConfigPath),
                new PhpSpecMutationConfigBuilder($this->tempDir, $phpSpecConfigPath, $this->projectDir),
                new PhpSpecArgumentsAndOptionsBuilder(),
                $this->versionParser
            );
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Invalid name of test framework. Available names are: %s',
                implode(', ', [TestFrameworkTypes::PHPUNIT, TestFrameworkTypes::PHPSPEC])
            )
        );
    }
}
