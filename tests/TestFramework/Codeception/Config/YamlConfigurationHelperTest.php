<?php
/**
 * Copyright © 2018 Tobias Stadler
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\TestFramework\Codeception\Config;

use Infection\Filesystem\Filesystem;
use Infection\TestFramework\Codeception\Config\YamlConfigurationHelper;
use Infection\Utils\TmpDirectoryCreator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class YamlConfigurationHelperTest extends TestCase
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

        $this->projectDir = __DIR__ . '/../../../Fixtures/Files/codeception/project-path';
    }

    protected function tearDown()
    {
        $this->filesystem->remove($this->workspace);
    }

    public function test_it_can_build_config_with_default_paths()
    {
        $originalContent = '';
        $configurationHelper = new YamlConfigurationHelper($this->tempDir, $this->projectDir, $originalContent, []);

        $config = Yaml::parse($configurationHelper->getTransformedConfig());

        $this->assertSame(realpath($this->projectDir . '/tests'), realpath($config['paths']['tests']));
        $this->assertSame(realpath($this->tempDir . '/.'), realpath($config['paths']['output']));
        $this->assertSame(realpath($this->projectDir . '/tests/_data'), realpath($config['paths']['data']));
        $this->assertSame(realpath($this->projectDir . '/tests/_support'), realpath($config['paths']['support']));
        $this->assertSame(realpath($this->projectDir . '/tests/_envs'), realpath($config['paths']['envs']));
    }

    public function test_it_can_build_config_with_custom_paths()
    {
        $originalContent = <<<YAML
paths:
    tests: test
    output: test/output
    data: test/data
    support: test/support
    envs: test/envs
YAML;
        $configurationHelper = new YamlConfigurationHelper($this->tempDir, $this->projectDir, $originalContent, []);

        $config = Yaml::parse($configurationHelper->getTransformedConfig('output'));

        $this->assertSame(realpath($this->projectDir . '/test'), realpath($config['paths']['tests']));
        $this->assertSame(realpath($this->tempDir . '/output'), realpath($config['paths']['output']));
        $this->assertSame(realpath($this->projectDir . '/test/data'), realpath($config['paths']['data']));
        $this->assertSame(realpath($this->projectDir . '/test/support'), realpath($config['paths']['support']));
        $this->assertSame(realpath($this->projectDir . '/test/envs'), realpath($config['paths']['envs']));
    }

    public function test_it_can_build_config_with_empty_coverage()
    {
        $originalContent = '';
        $configurationHelper = new YamlConfigurationHelper($this->tempDir, $this->projectDir, $originalContent, ['src']);

        $config = Yaml::parse($configurationHelper->getTransformedConfig());

        $this->assertSame(true, $config['coverage']['enabled']);
        $this->assertSame([self::rp($this->projectDir . '/src/*')], array_map(YamlConfigurationHelperTest::class . '::rp', $config['coverage']['include']));
        $this->assertSame([], $config['coverage']['exclude']);
    }

    public function test_it_can_build_config_with_disabled_coverage()
    {
        $originalContent = <<<YAML
coverage:
    enabled: true
    include:
        - abc
    exclude:
        - def
YAML;
        $configurationHelper = new YamlConfigurationHelper($this->tempDir, $this->projectDir, $originalContent, ['src']);

        $config = Yaml::parse($configurationHelper->getTransformedConfig('.', false));

        $this->assertSame(false, $config['coverage']['enabled']);
        $this->assertSame([], $config['coverage']['include']);
        $this->assertSame([], $config['coverage']['exclude']);
    }

    public function test_it_can_build_config_with_enabled_coverage()
    {
        $originalContent = <<<YAML
coverage:
    enabled: false
    include:
        - abc
    exclude:
        - def
YAML;
        $configurationHelper = new YamlConfigurationHelper($this->tempDir, $this->projectDir, $originalContent, ['src']);

        $config = Yaml::parse($configurationHelper->getTransformedConfig('.', true));

        $this->assertSame(true, $config['coverage']['enabled']);
        $this->assertSame([self::rp($this->projectDir . '/src/*')], array_map(YamlConfigurationHelperTest::class . '::rp', $config['coverage']['include']));
        $this->assertSame([], $config['coverage']['exclude']);
    }

    private static function rp(string $path) : string
    {
        return realpath(substr($path, 0, -1)) . '*';
    }
}
