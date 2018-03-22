<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework;

use Infection\Config\InfectionConfig;
use Infection\Finder\TestFrameworkFinder;
use Infection\TestFramework\Codeception\Adapter\CodeceptionAdapter;
use Infection\TestFramework\Codeception\CommandLine\ArgumentsAndOptionsBuilder as CodeceptionArgumentsAndOptionsBuilder;
use Infection\TestFramework\Codeception\Config\Builder\InitialConfigBuilder as CodeceptionInitialConfigBuilder;
use Infection\TestFramework\Codeception\Config\Builder\MutationConfigBuilder as CodeceptionMutationConfigBuilder;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
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
        string $tmpDir,
        string $projectDir,
        TestFrameworkConfigLocator $configLocator,
        XmlConfigurationHelper $xmlConfigurationHelper,
        string $jUnitFilePath,
        InfectionConfig $infectionConfig,
        VersionParser $versionParser
    ) {
        $this->tmpDir = $tmpDir;
        $this->configLocator = $configLocator;
        $this->xmlConfigurationHelper = $xmlConfigurationHelper;
        $this->projectDir = $projectDir;
        $this->jUnitFilePath = $jUnitFilePath;
        $this->infectionConfig = $infectionConfig;
        $this->versionParser = $versionParser;
    }

    public function create(string $adapterName, bool $skipCoverage): AbstractTestFrameworkAdapter
    {
        if ($adapterName === TestFrameworkTypes::PHPUNIT) {
            $phpUnitConfigPath = $this->configLocator->locate(TestFrameworkTypes::PHPUNIT);
            $phpUnitConfigContent = file_get_contents($phpUnitConfigPath);

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

        if ($adapterName === TestFrameworkTypes::CODECEPTION) {
            $codeceptionConfigPath = $this->configLocator->locate(TestFrameworkTypes::CODECEPTION);
            $codeceptionConfigContent = file_get_contents($codeceptionConfigPath);

            return new CodeceptionAdapter(
                new TestFrameworkFinder(CodeceptionAdapter::EXECUTABLE),
                new CodeceptionInitialConfigBuilder($this->tmpDir, $this->projectDir, $codeceptionConfigContent, $this->infectionConfig->getSourceDirs()),
                new CodeceptionMutationConfigBuilder($this->tmpDir, $this->projectDir, $codeceptionConfigContent),
                new CodeceptionArgumentsAndOptionsBuilder($this->tmpDir),
                $this->versionParser
            );
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Invalid name of test framework. Available names are: %s',
                implode(', ', TestFrameworkTypes::TYPES)
            )
        );
    }
}
