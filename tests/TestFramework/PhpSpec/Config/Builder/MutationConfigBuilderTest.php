<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpSpec\Config\Builder;

use Symfony\Component\Filesystem\Filesystem;
use Infection\Mutant\Mutant;
use Infection\Mutation;
use Infection\TestFramework\PhpSpec\Config\Builder\MutationConfigBuilder;
use Infection\Utils\TmpDirectoryCreator;
use Mockery;

class MutationConfigBuilderTest extends Mockery\Adapter\Phpunit\MockeryTestCase
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

    protected function setUp()
    {
        $this->workspace = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'infection-test' . \microtime(true) . \random_int(100, 999);

        $this->fileSystem = new Filesystem();
        $this->tmpDir = (new TmpDirectoryCreator($this->fileSystem))->createAndGet($this->workspace);
    }

    protected function tearDown()
    {
        $this->fileSystem->remove($this->workspace);
    }

    public function test_it_builds_path_to_mutation_config_file()
    {
        $projectDir = '/project/dir';
        $originalYamlConfigPath = __DIR__ . '/../../../../Fixtures/Files/phpspec/phpspec.yml';

        $mutation = Mockery::mock(Mutation::class);
        $mutation->shouldReceive('getHash')->andReturn('a1b2c3');
        $mutation->shouldReceive('getOriginalFilePath')->andReturn('/original/file/path');

        $mutant = Mockery::mock(Mutant::class);
        $mutant->shouldReceive('getMutation')->andReturn($mutation);
        $mutant->shouldReceive('getMutatedFilePath')->andReturn('/mutated/file/path');

        // TODO for PhpSpec pass file content as well
        // TODO test phpspec after that
        $builder = new MutationConfigBuilder($this->tmpDir, $originalYamlConfigPath, $projectDir);

        $this->assertSame($this->tmpDir . '/phpspecConfiguration.a1b2c3.infection.yml', $builder->build($mutant));
    }

    public function test_it_adds_original_bootstrap_file_to_custom_autoload()
    {
        $projectDir = '/project/dir';
        $originalYamlConfigPath = __DIR__ . '/../../../../Fixtures/Files/phpspec/phpspec.with.bootstrap.yml';

        $mutation = Mockery::mock(Mutation::class);
        $mutation->shouldReceive('getHash')->andReturn('a1b2c3');
        $mutation->shouldReceive('getOriginalFilePath')->andReturn('/original/file/path');

        $mutant = Mockery::mock(Mutant::class);
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
