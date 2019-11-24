<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpSpec\Config;

use Infection\TestFramework\Coverage\XMLLineCodeCoverage;
use Infection\TestFramework\PhpSpec\Config\InitialYamlConfiguration;
use Infection\TestFramework\PhpSpec\Config\NoCodeCoverageException;
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

    public function test_it_throws_exception_when_extensions_array_is_empty(): void
    {
        $configuration = $this->getConfigurationObject(['extensions' => []]);
        $this->expectException(NoCodeCoverageException::class);

        $configuration->getYaml();
    }

    public function test_it_throws_exception_when_extensions_array_is_not_present(): void
    {
        $configuration = $this->getConfigurationObject(['bootstrap' => '/path/to/adc']);
        $this->expectException(NoCodeCoverageException::class);

        $configuration->getYaml();
    }

    public function test_it_throws_exception_when_no_extensions_have_no_coverage_one(): void
    {
        $configuration = $this->getConfigurationObject(['extensions' => ['a' => []]]);
        $this->expectException(NoCodeCoverageException::class);

        $configuration->getYaml();
    }

    public function test_it_updates_code_coverage_file(): void
    {
        $configuration = $this->getConfigurationObject();

        $parsedYaml = Yaml::parse($configuration->getYaml());
        $expectedPath = $this->tempDir . '/' . XMLLineCodeCoverage::PHP_SPEC_COVERAGE_DIR;

        $this->assertSame($expectedPath, $parsedYaml['extensions']['PhpSpecCodeCoverageExtension']['output']['xml']);
    }

    public function test_it_removes_all_coverage_extensions_if_coverage_should_be_skipped(): void
    {
        $configuration = $this->getConfigurationObject(
            ['extensions' => ['CodeCoverage1' => [], 'CodeCoverage2' => []]],
            true
        );

        $parsedYaml = Yaml::parse($configuration->getYaml());

        $this->assertCount(0, $parsedYaml['extensions']);
    }

    public function test_it_preserves_options_form_coverage_extension(): void
    {
        $configuration = $this->getConfigurationObject();

        $parsedYaml = Yaml::parse($configuration->getYaml());

        $this->assertSame(['.'], $parsedYaml['extensions']['PhpSpecCodeCoverageExtension']['whitelist']);
    }

    protected function getConfigurationObject(array $configArray = [], bool $skipCoverage = false)
    {
        return new InitialYamlConfiguration($this->tempDir, $configArray ?: $this->defaultConfig, $skipCoverage);
    }
}
