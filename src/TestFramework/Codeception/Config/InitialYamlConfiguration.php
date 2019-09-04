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
        $pathToProjectDir = $this->getPathToProjectDir();
        $config = $this->originalConfig;

        $config = $this->updatePaths($config, $pathToProjectDir);

        $config['paths'] = [
            'tests'   => $config['paths']['tests'] ?? $pathToProjectDir . 'tests',
            'output'  => $this->tmpDir,
            'data'    => $config['paths']['data'] ?? $pathToProjectDir . 'tests/_data',
            'support' => $config['paths']['support'] ?? $pathToProjectDir . 'tests/_support',
            'envs'    => $config['paths']['envs'] ?? $pathToProjectDir . 'tests/_envs',
        ];

        $config['coverage'] = [
            'enabled' => true,
            'include' => array_map(
                static function ($dir) use ($pathToProjectDir) {
                    return $pathToProjectDir . trim($dir, '/') . '/*.php';
                },
                $config['coverage']['include'] ?? $this->srcDirs
            ),
            'exclude' => [],
        ];

        return Yaml::dump($config);
    }
}
