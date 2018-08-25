<?php
/**
 * Created by PhpStorm.
 * User: fenikkusu
 * Date: 8/25/18
 * Time: 12:45 AM
 */

namespace Infection\TestFramework\Config\Builder;

use Infection\Config\InfectionConfig;

/**
 * @internal
 */
abstract class AbstractBuilder
{
    /** @var InfectionConfig */
    private $infectionConfig;

    /** @var string */
    private $tempDirectory;

    /** @var string */
    private $configPath;

    public function __construct(InfectionConfig $infectionConfig, string $tempDirectory, string $configPath)
    {
        $this->infectionConfig = $infectionConfig;
        $this->tempDirectory   = $tempDirectory;
        $this->configPath      = $configPath;
    }

    protected function getInfectionConfig(): InfectionConfig
    {
        return $this->infectionConfig;
    }

    protected function getTempDirectory(): string
    {
        return $this->tempDirectory;
    }

    protected function getConfigPath(): string
    {
        return $this->configPath;
    }

    protected function readConfigFile(): string
    {
        $configContent = file_get_contents($this->getConfigPath());
        \assert(is_string($configContent));

        return $configContent;
    }
}
