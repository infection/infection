<?php

declare(strict_types=1);

namespace Infection\Tests\Process\ExecutableFinder;

use Infection\Php\ConfigBuilder;
use Infection\Process\ExecutableFinder\PhpExecutableFinder;
use PHPUnit\Framework\TestCase;

class PhpExecutableFinderTest extends TestCase
{
    /**
     * @var string
     */
    private $workspace;

    public function setUp()
    {
        putenv(ConfigBuilder::ENV_TEMP_PHP_CONFIG_PATH);

        $this->workspace = sys_get_temp_dir() . DIRECTORY_SEPARATOR . microtime(true) . random_int(100, 999);
        mkdir($this->workspace, 0777, true);
    }

    public function test_it_find_temp_php_config()
    {
        $finder = new PhpExecutableFinder();

        $tempConfig = $this->workspace . DIRECTORY_SEPARATOR . 'php.ini';

        touch($tempConfig);

        putenv(ConfigBuilder::ENV_TEMP_PHP_CONFIG_PATH . '=' . $tempConfig);

        $this->assertSame(['-c', $tempConfig], $finder->findArguments());
    }

    public function tearDown()
    {
        @unlink($this->workspace);
    }
}
