<?php

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Codeception\Config;

use Infection\TestFramework\Codeception\Config\MutationYamlConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class MutationYamlConfigurationTest extends TestCase
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

    private const INTERCEPTOR_PATH = '/path/to/interceptor.php';

    public function test_it_prepends_paths_with_relative_path_prefix(): void
    {
        $configuration = $this->buildConfiguration();

        $initialConfig = Yaml::parse($configuration->getYaml());

        $this->assertSame('../tests', $initialConfig['paths']['tests']);
        $this->assertSame('../tests/_data', $initialConfig['paths']['data']);
        $this->assertSame('../tests/_support', $initialConfig['paths']['support']);
        $this->assertSame('../tests/_envs', $initialConfig['paths']['envs']);
    }

    public function test_it_disables_coverage(): void
    {
        $configuration = $this->buildConfiguration();

        $initialConfig = Yaml::parse($configuration->getYaml());

        $this->assertFalse($initialConfig['coverage']['enabled'], 'Coverage must not be enabled in a config for Mutant');
    }

    public function test_it_sets_bootstrap_file(): void
    {
        $configuration = $this->buildConfiguration();

        $initialConfig = Yaml::parse($configuration->getYaml());

        $this->assertSame(self::INTERCEPTOR_PATH, $initialConfig['bootstrap']);
    }

    private function buildConfiguration(array $parsedConfig = self::DEFAULT_CONFIG): MutationYamlConfiguration
    {
        return new MutationYamlConfiguration(
            __DIR__ . '/../../../Fixtures/Files/codeception/tmp',
            __DIR__ . '/../../../Fixtures/Files/codeception',
            $parsedConfig,
            '1a2bc3',
            self::INTERCEPTOR_PATH
        );
    }
}
