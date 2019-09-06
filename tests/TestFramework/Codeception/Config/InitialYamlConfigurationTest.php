<?php

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Codeception\Config;

use Infection\TestFramework\Codeception\Config\InitialYamlConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class InitialYamlConfigurationTest extends TestCase
{
    private const DEFAULT_CONFIG = [
        'paths' => [
            'tests' =>  'tests',
            'output' =>  'tests/_output',
            'data' =>  'tests/_data',
            'support' =>  'tests/_support',
            'envs' =>  'tests/_envs',
        ],
        'actor_suffix' => 'Tester',
        'extensions' => [
            'enabled' => ['Codeception\Extension\RunFailed']
        ],
    ];

    public function test_it_prepends_paths_with_relative_path_prefix(): void
    {
        $configuration = $this->buildConfiguration();

        $initialConfig = Yaml::parse($configuration->getYaml());

        $this->assertSame('../tests', $initialConfig['paths']['tests']);
        $this->assertSame('../tests/_data', $initialConfig['paths']['data']);
        $this->assertSame('../tests/_support', $initialConfig['paths']['support']);
        $this->assertSame('../tests/_envs', $initialConfig['paths']['envs']);
    }

    public function test_it_sets_the_output_dir_to_tmp_dir(): void
    {
        $configuration = $this->buildConfiguration();

        $initialConfig = Yaml::parse($configuration->getYaml());

        $this->assertSame(__DIR__ . '/../../../Fixtures/Files/codeception/tmp', $initialConfig['paths']['output']);
    }

    public function test_it_adds_relative_default_paths_if_not_set(): void
    {
        $config = self::DEFAULT_CONFIG;
        unset($config['paths']['envs']);

        $configuration = $this->buildConfiguration($config);

        $initialConfig = Yaml::parse($configuration->getYaml());

        $this->assertSame('../tests/_envs', $initialConfig['paths']['envs']);
    }

    public function test_it_enables_coverage(): void
    {
        $configuration = $this->buildConfiguration();

        $initialConfig = Yaml::parse($configuration->getYaml());

        $this->assertTrue($initialConfig['coverage']['enabled'], 'Coverage must be enabled with is not skipped by the user');
    }

    public function test_it_does_not_enable_coverage_when_skipped_by_user(): void
    {
        $configuration = $this->buildConfigurationWithSkippedCoverage();

        $initialConfig = Yaml::parse($configuration->getYaml());

        $this->assertFalse($initialConfig['coverage']['enabled'], 'Coverage must not be enabled when skipped by the user');
    }

    public function test_it_prepends_existing_include_and_exclude_coverage_keys_with_relative_path_prefix(): void
    {
        $configWithCoverageIncludeAndExcludeDirs = array_merge(
            self::DEFAULT_CONFIG,
            [
                'coverage' => [
                    'include' => [
                        'firstDir',
                        'secondDir'
                    ],
                    'exclude' => ['thirdDir'],
                ],
            ]
        );

        $configuration = $this->buildConfiguration($configWithCoverageIncludeAndExcludeDirs);

        $initialConfig = Yaml::parse($configuration->getYaml());

        $this->assertSame(
            [
                '../firstDir',
                '../secondDir',
            ],
            $initialConfig['coverage']['include']
        );

        $this->assertSame(['../thirdDir'], $initialConfig['coverage']['exclude']);
    }

    public function test_it_populates_include_coverage_key_from_src_folders_if_not_set(): void
    {
        $configuration = $this->buildConfiguration();

        $initialConfig = Yaml::parse($configuration->getYaml());

        $this->assertSame('../src/*.php', $initialConfig['coverage']['include'][0]);
    }

    private function buildConfigurationWithSkippedCoverage(): InitialYamlConfiguration
    {
        return $this->buildConfiguration(self::DEFAULT_CONFIG, true);
    }

    private function buildConfiguration(array $parsedConfig = self::DEFAULT_CONFIG, bool $skipCoverage = false): InitialYamlConfiguration
    {
        return new InitialYamlConfiguration(
                __DIR__ . '/../../../Fixtures/Files/codeception/tmp',
            __DIR__ . '/../../../Fixtures/Files/codeception',
            $parsedConfig,
            $skipCoverage,
            ['src']
        );
    }
}
