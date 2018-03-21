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
use Infection\Utils\TmpDirectoryCreator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class MutationConfigBuilderTest extends MockeryTestCase
{
    /**
     *@var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $workspace;

    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var string
     */
    private $projectDir;

    protected function setUp()
    {
        $this->filesystem = new Filesystem();

        $this->workspace = sys_get_temp_dir() . '/infection-test' . \microtime(true) . \random_int(100, 999);
        $this->tempDir = (new TmpDirectoryCreator($this->filesystem))->createAndGet($this->workspace);

        $this->projectDir = __DIR__ . '/../../../../Fixtures/Files/codeception/project-path';
    }

    protected function tearDown()
    {
        $this->filesystem->remove($this->workspace);
    }

    public function test_it_can_build_mutation_config()
    {
        $originalContent = '';
        $initialConfigBuilder = new MutationConfigBuilder($this->tempDir, $this->projectDir, $originalContent, ['src']);

        $mutation = Mockery::mock(Mutation::class);
        $mutation->shouldReceive('getHash')->andReturn('a1b2c3');
        $mutation->shouldReceive('getOriginalFilePath')->andReturn('/original/file/path');

        $mutant = Mockery::mock(Mutant::class);
        $mutant->shouldReceive('getMutation')->andReturn($mutation);
        $mutant->shouldReceive('getMutatedFilePath')->andReturn('/mutated/file/path');

        $config = Yaml::parseFile($initialConfigBuilder->build($mutant));

        mkdir($config['paths']['output']);

        $this->assertSame(realpath($this->projectDir . '/tests'), realpath($config['paths']['tests']));
        $this->assertSame(realpath($this->tempDir . '/a1b2c3'), realpath($config['paths']['output']));
        $this->assertSame(realpath($this->projectDir . '/tests/_data'), realpath($config['paths']['data']));
        $this->assertSame(realpath($this->projectDir . '/tests/_support'), realpath($config['paths']['support']));
        $this->assertSame(realpath($this->projectDir . '/tests/_envs'), realpath($config['paths']['envs']));
        $this->assertSame(false, $config['coverage']['enabled']);
        $this->assertSame([], $config['coverage']['include']);
        $this->assertSame([], $config['coverage']['exclude']);
    }
}
