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

namespace Infection\Tests\TestFramework\PhpSpec\Config\Builder;

use Infection\Mutant\Mutant;
use Infection\Mutation;
use Infection\TestFramework\PhpSpec\Config\Builder\MutationConfigBuilder;
use Infection\Tests\FileSystem\FileSystemTestCase;

/**
 * @group integration Requires some I/O operations
 */
final class MutationConfigBuilderTest extends FileSystemTestCase
{
    private const MUTATION_HASH = 'a1b2c3';
    private const ORIGINAL_FILE_PATH = '/original/file/path';
    private const MUTATED_FILE_PATH = '/mutated/file/path';

    public function test_it_builds_path_to_mutation_config_file(): void
    {
        $projectDir = '/project/dir';
        $originalYamlConfigPath = __DIR__ . '/../../../../Fixtures/Files/phpspec/phpspec.yml';

        // TODO for PhpSpec pass file content as well
        // TODO test phpspec after that
        $builder = new MutationConfigBuilder($this->tmp, $originalYamlConfigPath, $projectDir);

        $this->assertSame(
            $this->tmp . '/phpspecConfiguration.a1b2c3.infection.yml',
            $builder->build(
                [],
                self::MUTATED_FILE_PATH,
                self::MUTATION_HASH,
                self::ORIGINAL_FILE_PATH
            )
        );
    }

    public function test_it_adds_original_bootstrap_file_to_custom_autoload(): void
    {
        $projectDir = '/project/dir';
        $originalYamlConfigPath = __DIR__ . '/../../../../Fixtures/Files/phpspec/phpspec.with.bootstrap.yml';

        $mutation = $this->createMock(Mutation::class);
        $mutation->method('getHash')
            ->willReturn('a1b2c3');
        $mutation->method('getOriginalFilePath')
            ->willReturn('/original/file/path');

        $mutant = $this->createMock(Mutant::class);
        $mutant->method('getMutation')
            ->willReturn($mutation);
        $mutant->method('getMutantFilePath')
            ->willReturn('/mutated/file/path');

        $builder = new MutationConfigBuilder($this->tmp, $originalYamlConfigPath, $projectDir);

        $this->assertSame(
            $this->tmp . '/phpspecConfiguration.a1b2c3.infection.yml',
            $builder->build(
                [],
                self::MUTATED_FILE_PATH,
                self::MUTATION_HASH,
                self::ORIGINAL_FILE_PATH
            )
        );
        $this->assertStringContainsString(
            "require_once '/project/dir/bootstrap.php';",
            file_get_contents($this->tmp . '/interceptor.phpspec.autoload.a1b2c3.infection.php')
        );
    }
}
