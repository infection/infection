<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\StreamWrapper;

use Infection\StreamWrapper\IncludeInterceptor;
use PHPUnit\Framework\Error\Warning;
use PHPUnit\Framework\TestCase;

/**
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
 * but still are required to be implemented by a full wrapper
 */
final class IncludeInterceptorTest extends TestCase
{
    private static $files = [];

    public static function setUpBeforeClass(): void
    {
        foreach (range(1, 3) as $number) {
            $tempnam = tempnam('', basename(__FILE__, 'php'));
            file_put_contents($tempnam, "<?php return $number;");
            self::$files[$number] = $tempnam;
        }
    }

    protected function tearDown(): void
    {
        @IncludeInterceptor::disable();
    }

    public static function tearDownAfterClass(): void
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
    public function test_it_throws_an_exception_if_not_configured(): void
    {
        $this->expectException(\RuntimeException::class);
        IncludeInterceptor::enable();
    }

    public function test_it_throws_an_exception_if_target_not_exists(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        IncludeInterceptor::intercept('', '');
    }

    public function test_it_throws_an_exception_if_destination_not_exists(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        IncludeInterceptor::intercept(self::$files[1], '');
    }

    public function test_it_not_intercepts_when_not_included(): void
    {
        $before = file_get_contents(self::$files[1]);
        // Sanity check
        $this->assertStringContainsString('1', $before);

        IncludeInterceptor::intercept(self::$files[1], self::$files[2]);
        IncludeInterceptor::enable();

        $after = file_get_contents(self::$files[1]);

        IncludeInterceptor::disable();

        $this->assertSame($before, $after);
    }

    public function test_it_intercepts_file_with_another(): void
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

    public function test_it_does_not_intercept_file_where_should_not(): void
    {
        $before = include self::$files[3];
        $this->assertSame(3, $before);

        IncludeInterceptor::intercept(self::$files[1], self::$files[2]);
        IncludeInterceptor::enable();

        $after = include self::$files[3];

        IncludeInterceptor::disable();

        $this->assertSame($before, $after);
    }

    public function test_passthrough_file_methods_pass(): void
    {
        if (\PHP_SAPI === 'phpdbg') {
            $this->markTestSkipped('Running this test on PHPDBG has issues with FD_SETSIZE. Consider removing this if that issue has been fixed.');
        }
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

    public function test_passthrough_dir_methods_pass(): void
    {
        IncludeInterceptor::intercept(self::$files[1], self::$files[2]);
        IncludeInterceptor::enable();

        /*
         * PHP will give a warning if our stream wrapper
         * cannot handle any of these
         */

        $this->assertGreaterThan(0, \count(glob(__DIR__ . '/*')));

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

    public function test_it_re_enables_interceptor_after_file_not_found_with_fopen(): void
    {
        $expected = include self::$files[2];

        IncludeInterceptor::intercept(self::$files[1], self::$files[2]);
        IncludeInterceptor::enable();

        try {
            fopen('file-does-not-exist.txt', 'r');
        } catch (Warning $e) {
            $after = include self::$files[1];

            $this->assertSame($after, $expected);

            return;
        }

        $this->fail('Badly set up test, exception was not thrown');
    }

    public function test_it_re_enables_interceptor_after_file_not_found_with_include(): void
    {
        $expected = include self::$files[2];

        IncludeInterceptor::intercept(self::$files[1], self::$files[2]);
        IncludeInterceptor::enable();

        try {
            include 'file-does-not-exist.txt';
        } catch (Warning $e) {
            $after = include self::$files[1];

            $this->assertSame($after, $expected);

            return;
        }

        $this->fail('Badly set up test, exception was not thrown');
    }

    public function test_it_re_enables_interceptor_after_directory_not_found_with_opendir(): void
    {
        $expected = include self::$files[2];

        IncludeInterceptor::intercept(self::$files[1], self::$files[2]);
        IncludeInterceptor::enable();

        try {
            opendir('dir-does-not-exist');
        } catch (Warning $e) {
            $after = include self::$files[1];

            $this->assertSame($after, $expected);

            return;
        }

        $this->fail('Badly set up test, exception was not thrown');
    }

    public function test_it_re_enables_interceptor_after_directory_already_exist_with_mkdir(): void
    {
        $expected = include self::$files[2];

        IncludeInterceptor::intercept(self::$files[1], self::$files[2]);
        IncludeInterceptor::enable();

        try {
            mkdir(__DIR__);
        } catch (Warning $e) {
            $after = include self::$files[1];

            $this->assertSame($after, $expected);

            return;
        }

        $this->fail('Badly set up test, exception was not thrown');
    }

    public function test_it_re_enables_interceptor_after_file_not_found_with_rename(): void
    {
        $expected = include self::$files[2];

        IncludeInterceptor::intercept(self::$files[1], self::$files[2]);
        IncludeInterceptor::enable();

        try {
            rename('file-does-not-exist.txt', 'something-else.txt');
        } catch (Warning $e) {
            $after = include self::$files[1];

            $this->assertSame($after, $expected);

            return;
        }

        $this->fail('Badly set up test, exception was not thrown');
    }

    public function test_it_re_enables_interceptor_after_directory_not_found_with_rmdir(): void
    {
        $expected = include self::$files[2];

        IncludeInterceptor::intercept(self::$files[1], self::$files[2]);
        IncludeInterceptor::enable();

        try {
            rmdir('dir-does-not-exist');
        } catch (Warning $e) {
            $after = include self::$files[1];

            $this->assertSame($after, $expected);

            return;
        }

        $this->fail('Badly set up test, exception was not thrown');
    }

    public function test_it_re_enables_interceptor_after_file_not_found_with_stream_metadata(): void
    {
        $expected = include self::$files[2];

        IncludeInterceptor::intercept(self::$files[1], self::$files[2]);
        IncludeInterceptor::enable();

        try {
            touch('/');
        } catch (Warning $e) {
            $after = include self::$files[1];

            $this->assertSame($after, $expected);

            return;
        }

        $this->fail('Badly set up test, exception was not thrown');
    }

    public function test_it_re_enables_interceptor_after_file_not_found_with_unlink(): void
    {
        $expected = include self::$files[2];

        IncludeInterceptor::intercept(self::$files[1], self::$files[2]);
        IncludeInterceptor::enable();

        try {
            unlink('file-does-not-exist.txt');
        } catch (Warning $e) {
            $after = include self::$files[1];

            $this->assertSame($after, $expected);

            return;
        }

        $this->fail('Badly set up test, exception was not thrown');
    }

    public function test_it_re_enables_interceptor_after_file_not_found_with_url_stat(): void
    {
        $expected = include self::$files[2];

        IncludeInterceptor::intercept(self::$files[1], self::$files[2]);
        IncludeInterceptor::enable();

        try {
            copy('file-does-not-exist.txt', 'somewhere-else.txt');
        } catch (Warning $e) {
            $after = include self::$files[1];

            $this->assertSame($after, $expected);

            return;
        }

        $this->fail('Badly set up test, exception was not thrown');
    }
}
