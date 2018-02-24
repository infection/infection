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
            'SomeOtherExtension' => [],
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

    protected function getConfigurationObject(array $configArray = [], bool $skipCoverage = false)
    {
        return new InitialYamlConfiguration($this->tempDir, $configArray ?: $this->defaultConfig, $skipCoverage);
    }

    /**
     * @expectedException \Infection\TestFramework\PhpSpec\Config\NoCodeCoverageException
     */
    public function test_it_throws_exception_when_extensions_array_is_empty()
    {
        $configuration = $this->getConfigurationObject(['extensions' => []]);

        $configuration->getYaml();
    }

    /**
     * @expectedException \Infection\TestFramework\PhpSpec\Config\NoCodeCoverageException
     */
    public function test_it_throws_exception_when_extensions_array_is_not_present()
    {
        $configuration = $this->getConfigurationObject(['bootstrap' => '/path/to/adc']);

        $configuration->getYaml();
    }

    /**
     * @expectedException \Infection\TestFramework\PhpSpec\Config\NoCodeCoverageException
     */
    public function test_it_throws_exception_when_no_extensions_have_no_coverage_one()
    {
        $configuration = $this->getConfigurationObject(['extensions' => ['a' => []]]);

        $configuration->getYaml();
    }

    public function test_it_updates_code_coverage_file()
    {
        $configuration = $this->getConfigurationObject();

        $parsedYaml = Yaml::parse($configuration->getYaml());
        $expectedPath = $this->tempDir . '/' . CodeCoverageData::PHP_SPEC_COVERAGE_DIR;

        $this->assertSame($expectedPath, $parsedYaml['extensions']['PhpSpecCodeCoverageExtension']['output']['xml']);
    }

    public function test_it_removes_all_coverage_extensions_if_coverage_should_be_skipped()
    {
        $configuration = $this->getConfigurationObject(
            ['extensions' => ['CodeCoverage1' => [], 'CodeCoverage2' => []]],
            true
        );

        $parsedYaml = Yaml::parse($configuration->getYaml());

        $this->assertCount(0, $parsedYaml['extensions']);
    }

    public function test_it_preserves_options_form_coverage_extension()
    {
        $configuration = $this->getConfigurationObject();

        $parsedYaml = Yaml::parse($configuration->getYaml());

        $this->assertSame(['.'], $parsedYaml['extensions']['PhpSpecCodeCoverageExtension']['whitelist']);
    }
}
