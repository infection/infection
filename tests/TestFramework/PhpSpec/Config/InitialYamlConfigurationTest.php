<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpSpec\Config;

use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\TestFramework\PhpSpec\Config\InitialYamlConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class InitialYamlConfigurationTest extends TestCase
{
    protected $tempDir = '/path/to/tmp';

    private $defaultConfig = [
        'extensions' => [
            'PhpSpecCodeCoverageExtension' => [
                'format' => ['xml', 'text'],
                'output' => [
                    'xml' => '/path',
                ],
                'whitelist' => ['.'],
            ],
        ],
        'bootstrap' => '/path/to/adc',
    ];

    protected function getConfigurationObject(array $configArray = [])
    {
        return new InitialYamlConfiguration($this->tempDir, $configArray ?: $this->defaultConfig);
    }

    /**
     * @expectedException \Infection\TestFramework\PhpSpec\Config\NoCodeCoverageException
     */
    public function test_it_throws_exception_when_no_coverage_extension()
    {
        $configuration = $this->getConfigurationObject(['extensions' => []]);

        $configuration->getYaml();
    }

    public function test_it_updates_code_coverage_file()
    {
        $configuration = $this->getConfigurationObject();

        $parsedYaml = Yaml::parse($configuration->getYaml());
        $expectedPath = $this->tempDir . '/' . CodeCoverageData::PHP_SPEC_COVERAGE_DIR;

        $this->assertSame($expectedPath, $parsedYaml['extensions']['PhpSpecCodeCoverageExtension']['output']['xml']);
    }

    public function test_it_preserves_options_form_coverage_extension()
    {
        $configuration = $this->getConfigurationObject();

        $parsedYaml = Yaml::parse($configuration->getYaml());

        $this->assertSame(['.'], $parsedYaml['extensions']['PhpSpecCodeCoverageExtension']['whitelist']);
    }
}
