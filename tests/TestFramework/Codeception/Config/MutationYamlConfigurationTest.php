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

namespace Infection\Tests\TestFramework\Codeception\Config;

use Infection\TestFramework\Codeception\Config\MutationYamlConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class MutationYamlConfigurationTest extends TestCase
{
    private const DEFAULT_CONFIG = [
        'paths' => [
            'tests' => 'tests',
            'output' => 'tests/_output',
            'data' => 'tests/_data',
            'support' => 'tests/_support',
            'envs' => 'tests/_envs',
        ],
        'actor_suffix' => 'Tester',
        'extensions' => [
            'enabled' => ['Codeception\Extension\RunFailed'],
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
