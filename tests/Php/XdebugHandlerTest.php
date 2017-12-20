<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Php;

use Infection\Php\ConfigBuilder;
use Infection\Php\PhpIniHelper;
use Infection\Tests\Php\Mock\XdebugHandlerMock;
use PHPUnit\Framework\TestCase;

class XdebugHandlerTest extends TestCase
{
    private static $env = [];

    public static function setUpBeforeClass()
    {
        // Save current state
        $names = [
            XdebugHandlerMock::ENV_DISABLE_XDEBUG,
            PhpIniHelper::ENV_ORIGINALS_PHP_INIS,
            ConfigBuilder::ENV_PHP_INI_SCAN_DIR,
            ConfigBuilder::ENV_TEMP_PHP_CONFIG_PATH,
        ];

        foreach ($names as $name) {
            self::$env[$name] = getenv($name);
        }
    }

    public static function tearDownAfterClass()
    {
        // Restore original state
        foreach (self::$env as $name => $value) {
            if (false !== $value) {
                putenv($name.'='.$value);
            } else {
                putenv($name);
            }
        }
    }

    protected function setUp()
    {
        putenv(PhpIniHelper::ENV_ORIGINALS_PHP_INIS);
        putenv(XdebugHandlerMock::ENV_DISABLE_XDEBUG);
        putenv(ConfigBuilder::ENV_TEMP_PHP_CONFIG_PATH);
    }

    public function test_it_restart_when_loaded()
    {
        $loaded = true;

        $xdebug = new XdebugHandlerMock($loaded);
        $xdebug->check();
        $this->assertTrue($xdebug->restarted);

        $this->assertInternalType('string', getenv(PhpIniHelper::ENV_ORIGINALS_PHP_INIS));
    }

    public function test_it_not_restart_when_loaded()
    {
        $loaded = false;

        $xdebug = new XdebugHandlerMock($loaded);
        $xdebug->check();
        $this->assertFalse($xdebug->restarted);
        $this->assertFalse(getenv(PhpIniHelper::ENV_ORIGINALS_PHP_INIS));
    }

    public function test_it_not_restart_when_loaded_and_allowed()
    {
        $loaded = true;
        putenv(XdebugHandlerMock::ENV_DISABLE_XDEBUG.'=1');

        $xdebug = new XdebugHandlerMock($loaded);
        $xdebug->check();
        $this->assertFalse($xdebug->restarted);
    }

    public function test_env_allow()
    {
        $loaded = true;

        $xdebug = new XdebugHandlerMock($loaded);
        $xdebug->check();
        $expected = XdebugHandlerMock::RESTART_HANDLE;
        $this->assertEquals($expected, getenv(XdebugHandlerMock::ENV_DISABLE_XDEBUG));

        // Mimic restart
        $xdebug = new XdebugHandlerMock($loaded);
        $xdebug->check();
        $this->assertFalse($xdebug->restarted);
        $this->assertFalse(getenv(XdebugHandlerMock::ENV_DISABLE_XDEBUG));
    }
}
