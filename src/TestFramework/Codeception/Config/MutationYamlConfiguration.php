<?php

declare(strict_types=1);


namespace Infection\TestFramework\Codeception\Config;

use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 */
final class MutationYamlConfiguration extends AbstractYamlConfiguration
{
    /**
     * @var string
     */
    private $mutationHash;

    /**
     * @var string
     */
    private $interceptorFilePath;

    public function __construct(string $tmpDir, string $projectDir, array $originalConfig, string $mutationHash, string $interceptorFilePath)
    {
        parent::__construct($tmpDir, $projectDir, $originalConfig);

        $this->mutationHash = $mutationHash;
        $this->interceptorFilePath = $interceptorFilePath;
    }

    public function getYaml(): string
    {
        $relativeFromTmpDirPathToProjectDir = $this->getRelativeFromTmpDirPathToProjectDir();
        $config = $this->originalConfig;

        $config = $this->updatePaths($config, $relativeFromTmpDirPathToProjectDir, $this->projectDir);

        $config['paths'] = [
            'tests'   => $config['paths']['tests'] ?? $relativeFromTmpDirPathToProjectDir . 'tests',
            'output'  => sprintf('%s/%s', $this->tmpDir, $this->mutationHash),
            'data'    => $config['paths']['data'] ?? $relativeFromTmpDirPathToProjectDir . 'tests/_data',
            'support' => $config['paths']['support'] ?? $relativeFromTmpDirPathToProjectDir . 'tests/_support',
            'envs'    => $config['paths']['envs'] ?? $relativeFromTmpDirPathToProjectDir . 'tests/_envs',
        ];

        $config['coverage'] = ['enabled' => false];
        $config['bootstrap'] = $this->interceptorFilePath;

        return Yaml::dump($config);
    }
}
