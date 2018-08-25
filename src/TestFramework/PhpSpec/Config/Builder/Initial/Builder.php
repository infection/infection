<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpSpec\Config\Builder\Initial;

use Infection\TestFramework\Config\Builder\Initial\AbstractBuilder;
use Infection\TestFramework\PhpSpec\Config\InitialYamlConfiguration;
use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 */
class Builder extends AbstractBuilder
{
    public function build(): string
    {
        $path = $this->buildPath();

        $yamlConfiguration = new InitialYamlConfiguration(
            $this->getTempDirectory(),
            Yaml::parseFile($this->getConfigPath()),
            $this->canSkipCoverage()
        );

        file_put_contents($path, $yamlConfiguration->getYaml());

        return $path;
    }

    private function buildPath(): string
    {
        return $this->getTempDirectory() . '/phpspecConfiguration.initial.infection.yml';
    }
}
