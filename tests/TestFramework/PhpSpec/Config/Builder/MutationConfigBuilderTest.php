<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2018, Maks Rafalko
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

namespace Infection\Tests\TestFramework\PhpSpec\Config\Builder;

use Infection\Mutant\MutantInterface;
use Infection\MutationInterface;
use Infection\TestFramework\PhpSpec\Config\Builder\MutationConfigBuilder;
use Infection\Utils\TmpDirectoryCreator;
use Mockery;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class MutationConfigBuilderTest extends Mockery\Adapter\Phpunit\MockeryTestCase
{
    private $tmpDir;

    /**
     * @var Filesystem
     */
    private $fileSystem;

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

    public function test_it_builds_path_to_mutation_config_file(): void
    {
        $projectDir = '/project/dir';
        $originalYamlConfigPath = __DIR__ . '/../../../../Fixtures/Files/phpspec/phpspec.yml';

        $mutation = Mockery::mock(MutationInterface::class);
        $mutation->shouldReceive('getHash')->andReturn('a1b2c3');
        $mutation->shouldReceive('getOriginalFilePath')->andReturn('/original/file/path');

        $mutant = Mockery::mock(MutantInterface::class);
        $mutant->shouldReceive('getMutation')->andReturn($mutation);
        $mutant->shouldReceive('getMutatedFilePath')->andReturn('/mutated/file/path');

        // TODO for PhpSpec pass file content as well
        // TODO test phpspec after that
        $builder = new MutationConfigBuilder($this->tmpDir, $originalYamlConfigPath, $projectDir);

        $this->assertSame($this->tmpDir . '/phpspecConfiguration.a1b2c3.infection.yml', $builder->build($mutant));
    }

    public function test_it_adds_original_bootstrap_file_to_custom_autoload(): void
    {
        $projectDir = '/project/dir';
        $originalYamlConfigPath = __DIR__ . '/../../../../Fixtures/Files/phpspec/phpspec.with.bootstrap.yml';

        $mutation = Mockery::mock(MutationInterface::class);
        $mutation->shouldReceive('getHash')->andReturn('a1b2c3');
        $mutation->shouldReceive('getOriginalFilePath')->andReturn('/original/file/path');

        $mutant = Mockery::mock(MutantInterface::class);
        $mutant->shouldReceive('getMutation')->andReturn($mutation);
        $mutant->shouldReceive('getMutatedFilePath')->andReturn('/mutated/file/path');

        $builder = new MutationConfigBuilder($this->tmpDir, $originalYamlConfigPath, $projectDir);

        $this->assertSame($this->tmpDir . '/phpspecConfiguration.a1b2c3.infection.yml', $builder->build($mutant));
        $this->assertContains(
            "require_once '/project/dir/bootstrap.php';",
            file_get_contents($this->tmpDir . '/interceptor.phpspec.autoload.a1b2c3.infection.php')
        );
    }
}
