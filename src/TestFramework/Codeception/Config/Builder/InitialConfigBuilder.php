<?php
/**
 * Copyright © 2018 Tobias Stadler
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\Codeception\Config\Builder;

use Infection\TestFramework\Codeception\Config\YamlConfigurationHelper;
use Infection\TestFramework\Config\InitialConfigBuilder as ConfigBuilder;

class InitialConfigBuilder implements ConfigBuilder
{
    /**
     * @var YamlConfigurationHelper
     */
    private $configurationHelper;

    public function __construct(string $tempDir, string $projectDir, string $originalConfig, array $srcDirs)
    {
        $this->configurationHelper = new YamlConfigurationHelper($tempDir, $projectDir, $originalConfig, $srcDirs);
    }

    public function build(string $version): string
    {
        $pathToInitialConfigFile = $this->configurationHelper->getTempDir() . DIRECTORY_SEPARATOR . 'codeception.initial.infection.yml';

        file_put_contents($pathToInitialConfigFile, $this->configurationHelper->getTransformedConfig());

        return $pathToInitialConfigFile;
    }
}
