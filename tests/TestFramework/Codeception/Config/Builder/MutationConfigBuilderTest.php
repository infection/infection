<?php
/**
 * Copyright © 2018 Tobias Stadler
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Codeception\Config\Builder;

use Infection\Mutant\Mutant;
use Infection\Mutation;
use Infection\TestFramework\Codeception\Config\Builder\MutationConfigBuilder;
use Infection\Utils\TempDirectoryCreator;
use PHPUnit\Framework\TestCase;
use Mockery;

class MutationConfigBuilderTest extends TestCase
{
    public function test_it_builds_path_to_mutation_config_file()
    {
        $tempDirCreator = new TempDirectoryCreator();

        $tempDir = $tempDirCreator->createAndGet('infection-test');
        $projectDir = 'project/dir';
        $originalConfigPath = 'original/config/path';

        $mutation = Mockery::mock(Mutation::class);
        $mutation->shouldReceive('getHash')->andReturn('a1b2c3');
        $mutation->shouldReceive('getOriginalFilePath')->andReturn('/original/file/path');

        $mutant = Mockery::mock(Mutant::class);
        $mutant->shouldReceive('getMutation')->andReturn($mutation);
        $mutant->shouldReceive('getMutatedFilePath')->andReturn('/mutated/file/path');

        $builder = new MutationConfigBuilder($tempDir, $projectDir, $originalConfigPath);

        $this->assertSame($originalConfigPath, $builder->build($mutant));
    }

    protected function tearDown()
    {
        Mockery::close();
    }
}
