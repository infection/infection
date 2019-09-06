<?php

declare(strict_types=1);


namespace Infection\TestFramework\Codeception\Config;


use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 */
final class InitialYamlConfiguration extends AbstractYamlConfiguration
{
    /**
     * @var string[]
     */
    protected $srcDirs;

    /**
     * @var bool
     */
    private $skipCoverage;

    public function __construct(string $tmpDir, string $projectDir, array $originalConfig, bool $skipCoverage, array $srcDirs)
    {
        parent::__construct($tmpDir, $projectDir, $originalConfig);

        $this->srcDirs = $srcDirs;
        $this->skipCoverage = $skipCoverage;
    }

    public function getYaml(): string
    {
        $relativeFromTmpDirPathToProjectDir = $this->getRelativeFromTmpDirPathToProjectDir();
        $config = $this->originalConfig;
        /** @var string $projectDirRealPath */
        $projectDirRealPath = realpath($this->projectDir);

        $config['paths'] = $this->updatePaths($config['paths'], $relativeFromTmpDirPathToProjectDir, $projectDirRealPath);

        $config['paths'] = [
            'tests' => $config['paths']['tests'] ?? $relativeFromTmpDirPathToProjectDir . 'tests',
            'output' => $this->tmpDir,
            'data' => $config['paths']['data'] ?? $relativeFromTmpDirPathToProjectDir . 'tests/_data',
            'support' => $config['paths']['support'] ?? $relativeFromTmpDirPathToProjectDir . 'tests/_support',
            'envs' => $config['paths']['envs'] ?? $relativeFromTmpDirPathToProjectDir . 'tests/_envs',
        ];

        if ($this->skipCoverage) {
            $config['coverage']['enabled'] = false;
        } else {
            $config['coverage'] = $this->prepareCoverageConfig($config, $relativeFromTmpDirPathToProjectDir);
        }

        return Yaml::dump($config);
    }

    private function prepareCoverageConfig(array $fullConfig, string $relativeFromTmpDirPathToProjectDir): array
    {
        $coverage = array_merge($fullConfig['coverage'] ?? [], ['enabled' => true]);

        if (array_key_exists('include', $coverage)) {
            $coverage['include'] = $this->prependWithRelativePathPrefix($coverage['include'], $relativeFromTmpDirPathToProjectDir);
        } else {
            // get all `srcDirs` from `infection.json` as a whitelist for coverage
            $coverage['include'] = array_map(
                static function ($dir) use ($relativeFromTmpDirPathToProjectDir) {
                    return $relativeFromTmpDirPathToProjectDir . trim($dir, '/') . '/*.php';
                },
                $this->srcDirs
            );
        }

        if (array_key_exists('exclude', $coverage)) {
            $coverage['exclude'] = $this->prependWithRelativePathPrefix($coverage['exclude'], $relativeFromTmpDirPathToProjectDir);
        }

        return $coverage;
    }

    /**
     * @param array<int, string> $dirs
     * @param string $relativeFromTmpDirPathToProjectDir
     * @return array<int, string>
     */
    private function prependWithRelativePathPrefix(array $dirs, string $relativeFromTmpDirPathToProjectDir): array
    {
        return array_map(
            static function ($dir) use ($relativeFromTmpDirPathToProjectDir) {
                return $relativeFromTmpDirPathToProjectDir . ltrim($dir, '/');
            },
            $dirs
        );
    }
}
