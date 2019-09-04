<?php

declare(strict_types=1);


namespace Infection\TestFramework\Codeception\Config\Builder;

use Infection\TestFramework\Codeception\Config\InitialYamlConfiguration;
use Infection\TestFramework\Config\InitialConfigBuilder as ConfigBuilder;
use Symfony\Component\Yaml\Yaml;

class InitialConfigBuilder implements ConfigBuilder
{
    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @var array
     */
    private $originalConfigContentParsed;

    /**
     * @var bool
     */
    private $skipCoverage;

    /**
     * @var string[]
     */
    private $srcDirs;

    public function __construct(string $tmpDir, string $projectDir, array $originalConfigContentParsed, bool $skipCoverage, array $srcDirs)
    {
        $this->tmpDir = $tmpDir;
        $this->projectDir = $projectDir;
        $this->originalConfigContentParsed = $originalConfigContentParsed;
        $this->srcDirs = $srcDirs;
        $this->skipCoverage = $skipCoverage;
    }

    public function build(string $version): string
    {
        $path = $this->tmpDir . '/codeception.initial.infection.yml';

        $yamlConfiguration = new InitialYamlConfiguration(
            $this->tmpDir,
            $this->projectDir,
            $this->originalConfigContentParsed,
            $this->skipCoverage,
            $this->srcDirs
        );

        file_put_contents($path, $yamlConfiguration->getYaml());

        return $path;
    }
}
