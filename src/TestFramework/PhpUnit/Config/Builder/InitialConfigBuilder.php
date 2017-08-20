<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config\Builder;

use Infection\TestFramework\Config\InitialConfigBuilder as ConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\InitialXmlConfiguration;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;

class InitialConfigBuilder implements ConfigBuilder
{
    /**
     * @var string
     */
    private $tempDirectory;
    /**
     * @var string
     */
    private $originalXmlConfigPath;
    /**
     * @var PathReplacer
     */
    private $pathReplacer;

    /**
     * @var string
     */
    private $jUnitFilePath;

    /**
     * @var array
     */
    private $srcDirs = [];

    public function __construct(string $tempDirectory, string $originalXmlConfigPath, PathReplacer $pathReplacer, string $jUnitFilePath, array $srcDirs)
    {
        $this->tempDirectory = $tempDirectory;
        $this->originalXmlConfigPath = $originalXmlConfigPath;
        $this->pathReplacer = $pathReplacer;
        $this->jUnitFilePath = $jUnitFilePath;
        $this->srcDirs = $srcDirs;
    }

    public function build(): string
    {
        $path = $this->buildPath();

        $xmlConfigurationFile = new InitialXmlConfiguration(
            $this->tempDirectory,
            $this->originalXmlConfigPath,
            $this->pathReplacer,
            $this->jUnitFilePath,
            $this->srcDirs
        );

        file_put_contents($path, $xmlConfigurationFile->getXml());

        return $path;
    }

    private function buildPath(): string
    {
        return $this->tempDirectory . '/phpunitConfiguration.initial.infection.xml';
    }
}
