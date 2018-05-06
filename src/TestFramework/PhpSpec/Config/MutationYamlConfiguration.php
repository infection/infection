<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpSpec\Config;

use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 */
final class MutationYamlConfiguration extends AbstractYamlConfiguration
{
    /**
     * @var string
     */
    protected $customAutoloadFilePath;

    public function __construct($tmpDir, array $parsedYaml, string $customAutoloadFilePath)
    {
        parent::__construct($tmpDir, $parsedYaml);

        $this->customAutoloadFilePath = $customAutoloadFilePath;
    }

    public function getYaml(): string
    {
        $config = $this->removeCodeCoverageExtension($this->parsedYaml);
        $config = $this->setCustomAutoLoaderPath($config);

        return Yaml::dump($config);
    }

    private function removeCodeCoverageExtension(array $parsedYaml): array
    {
        if (!$this->hasCodeCoverageExtension($parsedYaml)) {
            return $parsedYaml;
        }

        $filteredExtensions = [];

        foreach ($parsedYaml['extensions'] as $extensionName => $options) {
            if (!$this->isCodeCoverageExtension($extensionName)) {
                $filteredExtensions[$extensionName] = $options;
            }
        }

        return array_merge($parsedYaml, ['extensions' => $filteredExtensions]);
    }

    private function setCustomAutoLoaderPath(array $config): array
    {
        // bootstrap must be before other keys because of PhpSpec bug with populating container under
        // some circumstances
        return ['bootstrap' => $this->customAutoloadFilePath] + $config;
    }
}
