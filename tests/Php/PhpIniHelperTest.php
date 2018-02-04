<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Php;

use Infection\Php\PhpIniHelper;
use PHPUnit\Framework\TestCase;

class PhpIniHelperTest extends TestCase
{
    public static $envOriginal;

    public static function setUpBeforeClass()
    {
        // Save current state
        self::$envOriginal = getenv(PhpIniHelper::ENV_ORIGINALS_PHP_INIS);
    }

    public static function tearDownAfterClass()
    {
        // Restore original state
        if (false !== self::$envOriginal) {
            putenv(PhpIniHelper::ENV_ORIGINALS_PHP_INIS . '=' . self::$envOriginal);
        } else {
            putenv(PhpIniHelper::ENV_ORIGINALS_PHP_INIS);
        }
    }

    public function test_it_works_with_loaded_ini()
    {
        $paths = [
            'test.ini',
        ];

        $this->setEnv($paths);
        $this->assertSame($paths, PhpIniHelper::get());
    }

    public function test_it_works_without_loaded_ini()
    {
        $paths = [
            '',
            'one.ini',
            'two.ini',
        ];

        $this->setEnv($paths);
        $this->assertSame($paths, PhpIniHelper::get());
    }

    private function setEnv(array $paths)
    {
        putenv(PhpIniHelper::ENV_ORIGINALS_PHP_INIS . '=' . implode(PATH_SEPARATOR, $paths));
    }
}
