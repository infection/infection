<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpSpec\Config;

/**
 * @internal
 */
abstract class AbstractYamlConfiguration
{
    /**
     * @var string
     */
    protected $tempDirectory;

    /**
     * @var array
     */
    protected $parsedYaml;

    public function __construct(string $tmpDir, array $parsedYaml)
    {
        $this->tempDirectory = $tmpDir;
        $this->parsedYaml = $parsedYaml;
    }

    abstract public function getYaml(): string;

    protected function isCodeCoverageExtension(string $extensionName): bool
    {
        return strpos($extensionName, 'CodeCoverage') !== false;
    }

    protected function hasCodeCoverageExtension(array $parsedYaml): bool
    {
        if (!array_key_exists('extensions', $parsedYaml)) {
            return false;
        }

        foreach ($parsedYaml['extensions'] as $extensionName => $options) {
            if ($this->isCodeCoverageExtension($extensionName)) {
                return true;
            }
        }

        return false;
    }
}
