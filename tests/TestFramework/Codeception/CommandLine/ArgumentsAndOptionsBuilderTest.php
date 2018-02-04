<?php
/**
 * Copyright © 2018 Tobias Stadler
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Codeception\CommandLine;

use Infection\Mutant\Mutant;
use Infection\Mutation;
use Infection\TestFramework\Codeception\CommandLine\ArgumentsAndOptionsBuilder;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery;

class ArgumentsAndOptionsBuilderTest extends MockeryTestCase
{
    public function test_it_builds_correct_command()
    {
        $tempPath = '/temp/path';
        $configPath = '/config/path';

        $builder = new ArgumentsAndOptionsBuilder($tempPath);

        $command = $builder->build($configPath, '--verbose');

        $this->assertContains('run', $command);
        $this->assertContains('--no-colors', $command);
        $this->assertContains(sprintf('-o "paths: output: %s"', $tempPath), $command);
        $this->assertContains('-o "coverage: enabled: true"', $command);
        $this->assertContains('--coverage-phpunit ' . CodeCoverageData::CODECEPTION_COVERAGE_DIR, $command);
    }

    public function test_it_builds_correct_command_with_mutant()
    {
        $tempPath = '/temp/path';
        $configPath = '/config/path';

        $mutation = Mockery::mock(Mutation::class);
        $mutation->shouldReceive('getHash')->andReturn('a1b2c3');

        $mutant = Mockery::mock(Mutant::class);
        $mutant->shouldReceive('getMutation')->andReturn($mutation);

        $builder = new ArgumentsAndOptionsBuilder($tempPath);

        $command = $builder->build($configPath, '--verbose', $mutant);

        $this->assertContains('run', $command);
        $this->assertContains('--no-colors', $command);
        $this->assertContains(sprintf('-o "paths: output: %s"', $tempPath. '/' . $mutant->getMutation()->getHash()), $command);
        $this->assertContains('-o "coverage: enabled: false"', $command);
        $this->assertContains('--ext "Infection\TestFramework\Codeception\CustomAutoloadFilePath"', $command);
        $this->assertContains('--fail-fast', $command);
    }

    protected function tearDown()
    {
        Mockery::close();
    }
}
