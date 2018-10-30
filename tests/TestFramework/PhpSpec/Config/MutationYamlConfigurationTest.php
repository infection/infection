<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpSpec\Config;

use Infection\TestFramework\PhpSpec\Config\MutationYamlConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 */
final class MutationYamlConfigurationTest extends TestCase
{
    protected $tempDir = '/path/to/tmp';

    private $customAutoloadFilePath = '/custom/path';

    private $defaultConfig = [
        'extensions' => [
            'FirstExtension' => [],
            'PhpSpecCodeCoverageExtension' => [
                'format' => ['xml', 'text'],
                'output' => [
                    'xml' => '/path',
                ],
            ],
            'SomeOtherExtension' => ['option' => 123],
        ],
        'bootstrap' => '/path/to/adc',
    ];

    public function test_it_removes_code_coverage_extension(): void
    {
        $configuration = $this->getConfigurationObject();

        $parsedYaml = Yaml::parse($configuration->getYaml());

        $this->assertCount(2, $parsedYaml['extensions']);
        $this->assertArrayNotHasKey('PhpSpecCodeCoverageExtension', $parsedYaml['extensions']);
    }

    public function test_it_returns_same_extensions_when_no_coverage_extension_found(): void
    {
        $originalParsedYaml = ['bootstrap' => '/path/to/adc', 'extensions' => []];
        $configuration = $this->getConfigurationObject($originalParsedYaml);

        $parsedYaml = Yaml::parse($configuration->getYaml());

        $this->assertCount(0, $parsedYaml['extensions']);
    }

    public function test_it_sets_custom_autoloader_path(): void
    {
        $configuration = $this->getConfigurationObject();

        $parsedYaml = Yaml::parse($configuration->getYaml());

        $this->assertSame($this->customAutoloadFilePath, $parsedYaml['bootstrap']);
    }

    protected function getConfigurationObject(array $configArray = [])
    {
        return new MutationYamlConfiguration(
            $this->tempDir,
            $configArray ?: $this->defaultConfig,
            $this->customAutoloadFilePath
        );
    }
}
