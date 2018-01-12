<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpSpec\Config;

use Symfony\Component\Yaml\Yaml;

class InitialYamlConfiguration extends AbstractYamlConfiguration
{
    /**
     * @var string
     */
    protected $originalYamlConfigPath;

    public function getYaml(): string
    {
        if (!$this->hasCodeCoverageExtension($this->parsedYaml)) {
            throw new NoCodeCoverageException("No code coverage Extension detected for PhpSpec. \nWithout code coverage, running Infection is not useful.");
        }

        $this->updateCodeCoveragePath($this->parsedYaml);

        return Yaml::dump($this->parsedYaml);
    }
}
