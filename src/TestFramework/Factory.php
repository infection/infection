<?php
/**
 * Copyright © 2017 Maks Rafalko
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
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;

class Factory
{
    /**
     * @var string
     */
    private $tempDir;
    /**
     * @var PathReplacer
     */
    private $pathReplacer;

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

    public function __construct(string $tempDir, string $projectDir, TestFrameworkConfigLocator $configLocator, PathReplacer $pathReplacer, string $jUnitFilePath, InfectionConfig $infectionConfig)
    {
        $this->tempDir = $tempDir;
        $this->configLocator = $configLocator;
        $this->pathReplacer = $pathReplacer;
        $this->projectDir = $projectDir;
        $this->jUnitFilePath = $jUnitFilePath;
        $this->infectionConfig = $infectionConfig;
    }

    public function create($adapterName): AbstractTestFrameworkAdapter
    {
        if ($adapterName === PhpUnitAdapter::NAME) {
            $phpUnitConfigPath = $this->configLocator->locate(PhpUnitAdapter::NAME);

            return new PhpUnitAdapter(
                new TestFrameworkExecutableFinder(PhpUnitAdapter::NAME, $this->infectionConfig->getPhpUnitCustomPath()),
                new InitialConfigBuilder($this->tempDir, $phpUnitConfigPath, $this->pathReplacer, $this->jUnitFilePath, $this->infectionConfig->getSourceDirs()),
                new MutationConfigBuilder($this->tempDir, $phpUnitConfigPath, $this->pathReplacer, $this->projectDir),
                new ArgumentsAndOptionsBuilder()
            );
        }

        if ($adapterName === PhpSpecAdapter::NAME) {
            $phpSpecConfigPath = $this->configLocator->locate(PhpSpecAdapter::NAME);

            return new PhpSpecAdapter(
                new TestFrameworkExecutableFinder(PhpSpecAdapter::NAME),
                new PhpSpecInitialConfigBuilder($this->tempDir, $phpSpecConfigPath),
                new PhpSpecMutationConfigBuilder($this->tempDir, $phpSpecConfigPath, $this->projectDir),
                new PhpSpecArgumentsAndOptionsBuilder()
            );
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Invalid name of test framework. Available names are: %s',
                implode(', ', [PhpUnitAdapter::NAME, PhpSpecAdapter::NAME])
            )
        );
    }
}
