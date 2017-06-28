<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace TestFramework\PhpSpec\Config\Builder;

use Infection\Mutant\Mutant;
use Infection\Mutation;
use Infection\TestFramework\PhpSpec\Config\Builder\MutationConfigBuilder;
use Infection\Utils\TempDirectoryCreator;
use PHPUnit\Framework\TestCase;
use Mockery;

class MutationConfigBuilderTest extends TestCase
{
    public function test_it_builds_path_to_mutation_config_file()
    {
        $tempDirCreator = new TempDirectoryCreator();
        $tempDir = $tempDirCreator->createAndGet('infection-test');
        $projectDir = '/project/dir';
        $originalYamlConfigPath = __DIR__ . '/../../../../Files/phpspec/phpspec.yml';

        $mutation = Mockery::mock(Mutation::class);
        $mutation->shouldReceive('getHash')->andReturn('a1b2c3');
        $mutation->shouldReceive('getOriginalFilePath')->andReturn('/original/file/path');

        $mutant = Mockery::mock(Mutant::class);
        $mutant->shouldReceive('getMutation')->andReturn($mutation);
        $mutant->shouldReceive('getMutatedFilePath')->andReturn('/mutated/file/path');

        $builder = new MutationConfigBuilder($tempDir, $originalYamlConfigPath, $projectDir);

        $this->assertSame($tempDir . '/phpspecConfiguration.a1b2c3.infection.yml', $builder->build($mutant));
    }

    protected function tearDown()
    {
        Mockery::close();
    }
}