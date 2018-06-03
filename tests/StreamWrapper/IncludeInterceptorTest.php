<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\StreamWrapper;

use Infection\StreamWrapper\IncludeInterceptor;

/**
 * @internal
 *
 * Tests IncludeInterceptor for correct operation.
 *
 * We mainly interested in methods, essential to interception:
 *
 * stream_close
 * stream_eof
 * stream_open
 * stream_read
 * stream_stat
 * url_stat
 *
 * Other methods are not essential for interception to work,
 * but still are required to be implemented by a full wrapper.
 */
final class IncludeInterceptorTest extends \PHPUnit\Framework\TestCase
{
    private static $files = [];

    public static function setUpBeforeClass()
    {
        foreach (range(1, 3) as $number) {
            $tempnam = tempnam('', basename(__FILE__, 'php'));
            file_put_contents($tempnam, "<?php return $number;");
            self::$files[$number] = $tempnam;
        }
    }

    public static function tearDownAfterClass()
    {
        /*
         * We need to always disable so not to interfere with other tests
         * if any of our tests fail for any reason.
         *
         * Silenced a warning here: stream_wrapper_restore(): file:// was never changed, nothing to restore
         * (this warning will never happen in normal circumstances)
         */
        @IncludeInterceptor::disable();

        array_map('unlink', self::$files);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_it_throws_an_exception_if_not_configured()
    {
        $this->expectException(\RuntimeException::class);
        IncludeInterceptor::enable();
    }

    public function test_it_throws_an_exception_if_target_not_exists()
    {
        $this->expectException(\InvalidArgumentException::class);
        IncludeInterceptor::intercept('', '');
    }

    public function test_it_throws_an_exception_if_destination_not_exists()
    {
        $this->expectException(\InvalidArgumentException::class);
        IncludeInterceptor::intercept(self::$files[1], '');
    }

    public function test_it_not_intercepts_when_not_included()
    {
        $before = file_get_contents(self::$files[1]);
        // Sanity check
        $this->assertContains('1', $before);

        IncludeInterceptor::intercept(self::$files[1], self::$files[2]);
        IncludeInterceptor::enable();

        $after = file_get_contents(self::$files[1]);

        IncludeInterceptor::disable();

        $this->assertSame($before, $after);
    }

    public function test_it_intercepts_file_with_another()
    {
        $before = include self::$files[1];
        $this->assertSame(1, $before);

        IncludeInterceptor::intercept(self::$files[1], self::$files[2]);
        IncludeInterceptor::enable();

        $after = include self::$files[1];

        IncludeInterceptor::disable();

        $expected = include self::$files[2];

        $this->assertNotSame($before, $after);
        $this->assertSame($after, $expected);
    }

    public function test_it_does_not_intercept_file_where_should_not()
    {
        $before = include self::$files[3];
        $this->assertSame(3, $before);

        IncludeInterceptor::intercept(self::$files[1], self::$files[2]);
        IncludeInterceptor::enable();

        $after = include self::$files[3];

        IncludeInterceptor::disable();

        $this->assertSame($before, $after);
    }

    public function test_passthrough_file_methods_pass()
    {
        IncludeInterceptor::intercept(self::$files[1], self::$files[2]);
        IncludeInterceptor::enable();

        /*
         * PHP will give a warning if our stream wrapper
         * cannot handle any of these
         */

        $tempnam = tempnam('', basename(__FILE__, 'php'));

        $fp = fopen($tempnam, 'w+');
        flock($fp, LOCK_EX);
        fwrite($fp, 'test');
        fseek($fp, 0);
        $this->assertSame('test', fread($fp, 4));
        ftruncate($fp, 0);
        $streams = [$fp];
        stream_select($streams, $streams, $streams, 0);
        stream_set_blocking($fp, false);
        stream_set_timeout($fp, 0);
        stream_set_write_buffer($fp, 0);
        stream_set_read_buffer($fp, 0);
        fclose($fp);

        $this->assertSame(0, stat($tempnam)['size']);
        touch($tempnam);
        touch($tempnam, time());
        chown($tempnam, stat($tempnam)['uid']);
        chgrp($tempnam, stat($tempnam)['gid']);
        chmod($tempnam, stat($tempnam)['mode']);

        $newname = tempnam('', basename(__FILE__, 'php'));
        rename($tempnam, $newname);
        unlink($newname);

        IncludeInterceptor::disable();
    }

    public function test_passthrough_dir_methods_pass()
    {
        IncludeInterceptor::intercept(self::$files[1], self::$files[2]);
        IncludeInterceptor::enable();

        /*
         * PHP will give a warning if our stream wrapper
         * cannot handle any of these
         */

        $this->assertGreaterThan(0, count(glob(__DIR__ . '/*')));

        $tempnam = tempnam('', basename(__FILE__, 'php'));
        unlink($tempnam);

        mkdir($tempnam);

        $fp = opendir($tempnam);
        readdir($fp);
        rewinddir($fp);
        closedir($fp);

        $newname = tempnam('', basename(__FILE__, 'php'));
        unlink($newname);

        rename($tempnam, $newname);
        rmdir($newname);

        IncludeInterceptor::disable();
    }
}
