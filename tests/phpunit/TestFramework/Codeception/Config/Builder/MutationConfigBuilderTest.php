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

namespace Infection\Tests\TestFramework\Codeception\Config\Builder;

use Infection\Mutant\MutantInterface;
use Infection\MutationInterface;
use Infection\TestFramework\Codeception\Config\Builder\MutationConfigBuilder;
use Infection\TestFramework\Coverage\JUnitTestCaseSorter;
use Infection\Utils\TmpDirectoryCreator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class MutationConfigBuilderTest extends TestCase
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

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @var string
     */
    private $workspace;

    protected function setUp(): void
    {
        $this->workspace = sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'infection-test' . \microtime(true) . \random_int(100, 999);
        $this->fileSystem = new Filesystem();

        $this->tmpDir = (new TmpDirectoryCreator($this->fileSystem))->createAndGet($this->workspace);
    }

    protected function tearDown(): void
    {
        $this->fileSystem->remove($this->workspace);
    }

    public function test_it_creates_mutation_config_file(): void
    {
        $builder = $this->getMutationConfigBuilder();

        $expectedConfigPath = $this->tmpDir . '/codeceptionConfiguration.a1b2c3.infection.yaml';

        $this->assertSame($expectedConfigPath, $builder->build($this->getMutantMock()));
        $this->assertFileExists($expectedConfigPath);
    }

    public function test_it_creates_interceptor_file(): void
    {
        $builder = $this->getMutationConfigBuilder();

        $expectedConfigPath = $this->tmpDir . '/interceptor.codeception.a1b2c3.php';

        $builder->build($this->getMutantMock());

        $this->assertFileExists($expectedConfigPath);
    }

    public function test_it_does_not_add_original_bootstrap_to_the_created_config_file_if_not_exists(): void
    {
        $builder = $this->getMutationConfigBuilder();

        $builder->build($this->getMutantMock());

        $this->assertStringNotContainsString(
            'bootstrap',
            file_get_contents($this->tmpDir . '/interceptor.codeception.a1b2c3.php')
        );
    }

    public function test_adds_original_bootstrap_to_the_created_config_file_with_absolute_path(): void
    {
        $config = array_merge(
            self::DEFAULT_CONFIG,
            [
                'bootstrap' => '/original/bootstrap.php',
            ]
        );

        $builder = $this->getMutationConfigBuilder($config);

        $builder->build($this->getMutantMock());

        $this->assertStringContainsString(
            "require_once '/original/bootstrap.php';",
            file_get_contents($this->tmpDir . '/interceptor.codeception.a1b2c3.php')
        );
    }

    public function test_adds_original_bootstrap_to_the_created_config_file_with_relative_path(): void
    {
        $config = array_merge(
            self::DEFAULT_CONFIG,
            [
                'bootstrap' => 'original/bootstrap.php',
            ]
        );

        $builder = $this->getMutationConfigBuilder($config);

        $builder->build($this->getMutantMock());

        $this->assertStringContainsString(
            "tests/original/bootstrap.php';",
            file_get_contents($this->tmpDir . '/interceptor.codeception.a1b2c3.php')
        );
    }

    private function getMutationConfigBuilder(array $parsedConfig = self::DEFAULT_CONFIG): MutationConfigBuilder
    {
        return new MutationConfigBuilder(
            $this->fileSystem,
            $this->tmpDir,
            __DIR__ . '/../../../../Fixtures/Files/codeception',
            $parsedConfig,
            new JUnitTestCaseSorter()
        );
    }

    private function getMutantMock()
    {
        $mutation = $this->createMock(MutationInterface::class);
        $mutation->method('getHash')
            ->willReturn('a1b2c3');
        $mutation->method('getOriginalFilePath')
            ->willReturn('/original/file/path');

        $mutant = $this->createMock(MutantInterface::class);
        $mutant->method('getMutation')
            ->willReturn($mutation);
        $mutant->method('getMutatedFilePath')
            ->willReturn('/mutated/file/path');

        return $mutant;
    }
}
