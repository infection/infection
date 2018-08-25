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
use Infection\TestFramework\Config\TestFrameworkConfigLocatorInterface;
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

    public function __construct(
        string $tmpDir,
        string $projectDir,
        TestFrameworkConfigLocatorInterface $configLocator,
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

    private function checkClassExists(string $className, string $classType): self
    {
        if (!class_exists($className)) {
            throw new \LogicException('Framework ' . $classType . ' Should Be Named ' . $className);
        }

        return $this;
    }

    public function create(string $adapterName, bool $skipCoverage): AbstractTestFrameworkAdapter
    {
        if (!in_array($adapterName, TestFrameworkTypes::TYPES)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid name of test framework. Available names are: %s',
                    implode(', ', TestFrameworkTypes::TYPES)
                )
            );
        }

        $configPath = $this->configLocator->locate($adapterName);
        $configContent = file_get_contents($configPath);
        \assert(\is_string($configContent));

        $baseNamespace = __NAMESPACE__ . '\\' . $adapterName . '\\';

        $adapterClass          = $baseNamespace . $adapterName . 'Adapter';
        $initConfigClass       = $baseNamespace . '\\Config\\Builder\\InitialConfigBuilder';
        $mutationConfigClass   = $baseNamespace . '\\Config\\Builder\\MutationConfigBuilder';
        $argumentsBuilderClass = $baseNamespace . '\\' . $adapterName . 'ExtraOptions';

        $this->checkClassExists($adapterClass, 'Adapter')
            ->checkClassExists($initConfigClass, 'Initial Config Builder')
            ->checkClassExists($mutationConfigClass, 'Mutation Config Builder')
            ->checkClassExists($argumentsBuilderClass, 'Argument Builder Class');

        $testAdapter = new $adapterClass(
            new TestFrameworkFinder($adapterName, $this->infectionConfig->{'get' . $adapterName . 'CustomPath'}()),
            new $initConfigClass(
                $this->tmpDir,
                $configContent,
                $this->xmlConfigurationHelper,
                $this->jUnitFilePath,
                $this->infectionConfig->getSourceDirs(),
                $skipCoverage
            ),
            new $mutationConfigClass(
                $this->tmpDir,
                $configContent,
                $this->xmlConfigurationHelper,
                $this->projectDir
            ),
            new $argumentsBuilderClass(),
            $this->versionParser
        );

        return $testAdapter;
    }
}
