<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Php;

use Infection\Filesystem\Filesystem;
use Infection\Php\ConfigBuilder;
use PHPUnit\Framework\TestCase;

class ConfigBuilderTest extends TestCase
{
    public static $envOriginal;

    /**
     * @var string
     */
    private $workspace;

    public static function setUpBeforeClass()
    {
        // Save current state
        self::$envOriginal = getenv(ConfigBuilder::ENV_TEMP_PHP_CONFIG_PATH);
    }

    public static function tearDownAfterClass()
    {
        // Restore original state
        if (false !== self::$envOriginal) {
            putenv(ConfigBuilder::ENV_TEMP_PHP_CONFIG_PATH . '=' . self::$envOriginal);
        } else {
            putenv(ConfigBuilder::ENV_TEMP_PHP_CONFIG_PATH);
        }
    }

    protected function setUp()
    {
        $this->workspace = sys_get_temp_dir() . DIRECTORY_SEPARATOR . microtime(true) . random_int(100, 999);
        mkdir($this->workspace, 0777, true);
    }

    protected function tearDown()
    {
        (new Filesystem())->remove($this->workspace);
    }

    public function test_it_builds_return_existing_path()
    {
        $builder = new ConfigBuilder(sys_get_temp_dir());

        $file = $this->workspace . DIRECTORY_SEPARATOR . 'infectionTest';

        touch($file);

        $this->setEnv($file);

        $this->assertSame($file, $builder->build());
    }

    private function setEnv(string $path)
    {
        putenv(ConfigBuilder::ENV_TEMP_PHP_CONFIG_PATH . '=' . $path);
    }
}
