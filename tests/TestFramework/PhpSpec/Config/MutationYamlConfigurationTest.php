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

class MutationYamlConfigurationTest extends TestCase
{
    protected $tempDir = '/path/to/tmp';

    private $customAutoloadFilePath = '/custom/path';

    private $defaultConfig = [
        'extensions' => [
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

    protected function getConfigurationObject(array $configArray = [])
    {
        return new MutationYamlConfiguration(
            $this->tempDir,
            $configArray ?: $this->defaultConfig,
            $this->customAutoloadFilePath
        );
    }

    public function test_it_removes_code_coverage_extension()
    {
        $configuration = $this->getConfigurationObject();

        $parsedYaml = Yaml::parse($configuration->getYaml());

        $this->assertCount(1, $parsedYaml['extensions']);
        $this->assertArrayNotHasKey('PhpSpecCodeCoverageExtension', $parsedYaml['extensions']);
    }

    public function test_it_sets_custom_autoloader_path()
    {
        $configuration = $this->getConfigurationObject();

        $parsedYaml = Yaml::parse($configuration->getYaml());

        $this->assertSame($this->customAutoloadFilePath, $parsedYaml['bootstrap']);
    }
}
