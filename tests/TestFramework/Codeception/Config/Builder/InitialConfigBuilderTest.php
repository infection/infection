<?php
/**
 * Copyright © 2018 Tobias Stadler
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare (strict_types = 1);

namespace Infection\Tests\TestFramework\Codeception\Config\Builder;

use Infection\Filesystem\Filesystem;
use Infection\TestFramework\Codeception\Config\Builder\InitialConfigBuilder;
use Infection\Utils\TmpDirectoryCreator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class InitialConfigBuilderTest extends TestCase
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

    public function test_it_can_build_initial_config()
    {
        $originalContent = '';
        $initialConfigBuilder = new InitialConfigBuilder($this->tempDir, $this->projectDir, $originalContent, ['src']);

        $config = Yaml::parseFile($initialConfigBuilder->build());

        $this->assertSame(realpath($this->projectDir . '/tests'), realpath($config['paths']['tests']));
        $this->assertSame(realpath($this->tempDir . '/.'), realpath($config['paths']['output']));
        $this->assertSame(realpath($this->projectDir . '/tests/_data'), realpath($config['paths']['data']));
        $this->assertSame(realpath($this->projectDir . '/tests/_support'), realpath($config['paths']['support']));
        $this->assertSame(realpath($this->projectDir . '/tests/_envs'), realpath($config['paths']['envs']));
        $this->assertSame(true, $config['coverage']['enabled']);
        $this->assertSame([self::rp($this->projectDir . '/src/*')], array_map(InitialConfigBuilderTest::class . '::rp', $config['coverage']['include']));
        $this->assertSame([], $config['coverage']['exclude']);
    }

    private static function rp(string $path) : string
    {
        return realpath(substr($path, 0, -1)) . '*';
    }
}
