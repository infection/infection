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

    public function __construct(string $tmpDir, string $projectDir, array $originalConfig, bool $skipCoverage, string $mutationHash, string $interceptorFilePath)
    {
        parent::__construct($tmpDir, $projectDir, $originalConfig, $skipCoverage);

        $this->mutationHash = $mutationHash;
        $this->interceptorFilePath = $interceptorFilePath;
    }

    public function getYaml(): string
    {
        $pathToProjectDir = $this->getPathToProjectDir();
        $config = $this->originalConfig;

        $config = $this->updatePaths($config, $pathToProjectDir);

        $config['paths'] = [
            'tests'   => $config['paths']['tests'] ?? $pathToProjectDir . 'tests',
            'output'  => sprintf('%s/%s', $this->tmpDir, $this->mutationHash),
            'data'    => $config['paths']['data'] ?? $pathToProjectDir . 'tests/_data',
            'support' => $config['paths']['support'] ?? $pathToProjectDir . 'tests/_support',
            'envs'    => $config['paths']['envs'] ?? $pathToProjectDir . 'tests/_envs',
        ];

        $config['coverage'] = ['enabled' => false];
        $config['bootstrap'] = $this->interceptorFilePath;

        return Yaml::dump($config);
    }
}
