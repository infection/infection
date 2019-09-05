<?php

declare(strict_types=1);


namespace Infection\TestFramework\Codeception\Config;


use Symfony\Component\Yaml\Yaml;

class InitialYamlConfiguration extends AbstractYamlConfiguration
{
    /**
     * @var string[]
     */
    protected $srcDirs;

    public function __construct(string $tmpDir, string $projectDir, array $originalConfig, bool $skipCoverage, array $srcDirs)
    {
        parent::__construct($tmpDir, $projectDir, $originalConfig, $skipCoverage);

        $this->srcDirs = $srcDirs;
    }

    public function getYaml(): string
    {
        $relativeFromTmpDirPathToProjectDir = $this->getRelativeFromTmpDirPathToProjectDir();
        $config = $this->originalConfig;

        $config = $this->updatePaths($config, $relativeFromTmpDirPathToProjectDir, realpath($this->projectDir));

        $config['paths'] = [
            'tests'   => $config['paths']['tests'] ?? $relativeFromTmpDirPathToProjectDir . 'tests',
            'output'  => $this->tmpDir,
            'data'    => $config['paths']['data'] ?? $relativeFromTmpDirPathToProjectDir . 'tests/_data',
            'support' => $config['paths']['support'] ?? $relativeFromTmpDirPathToProjectDir . 'tests/_support',
            'envs'    => $config['paths']['envs'] ?? $relativeFromTmpDirPathToProjectDir . 'tests/_envs',
        ];

        $config['coverage'] = [
            'enabled' => true,
            'include' => array_map(
                static function ($dir) use ($relativeFromTmpDirPathToProjectDir) {
                    return $relativeFromTmpDirPathToProjectDir . trim($dir, '/') . '/*.php';
                },
                $config['coverage']['include'] ?? $this->srcDirs
            ),
            'exclude' => [],
        ];

        return Yaml::dump($config);
    }
}
