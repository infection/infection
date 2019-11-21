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

namespace Infection\StreamWrapper;

/**
 * @internal
 */
final class IncludeInterceptor
{
    private const STREAM_OPEN_FOR_INCLUDE = 0x00000080;

    /**
     * @var resource
     */
    public $context;

    /**
     * @var bool|resource
     */
    private $fp;

    /**
     * @var string
     */
    private static $intercept;

    /**
     * @var string
     */
    private static $replacement;

    public static function intercept($file, $with): void
    {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException(
                'File to intercept and replace does not exist: ' . $file
            );
        }

        if (!file_exists($with)) {
            throw new \InvalidArgumentException(
                'File to replace intercepted file with does not exist: ' . $file
            );
        }
        self::$intercept = $file;
        self::$replacement = $with;
    }

    public static function enable(): void
    {
        if (!isset(self::$intercept) || !isset(self::$replacement)) {
            throw new \RuntimeException(
                'Set a file to intercept and its replacement before enabling wrapper'
            );
        }
        stream_wrapper_unregister('file');
        stream_wrapper_register('file', __CLASS__);
    }

    public static function disable(): void
    {
        stream_wrapper_restore('file');
    }

    public function stream_open($path, $mode, $options)
    {
        self::disable();
        $including = (bool) ($options & self::STREAM_OPEN_FOR_INCLUDE);

        if ($including) {
            if ($path === self::$intercept || realpath($path) === self::$intercept) {
                $this->fp = fopen(self::$replacement, 'r');
                self::enable();

                return true;
            }
        }

        if (isset($this->context)) {
            $this->fp = fopen($path, $mode, (bool) $options, $this->context);
        } else {
            $this->fp = fopen($path, $mode, (bool) $options);
        }
        self::enable();

        return $this->fp !== false;
    }

    public function dir_closedir()
    {
        \assert(\is_resource($this->fp));

        closedir($this->fp);

        return true;
    }

    public function dir_opendir($path)
    {
        self::disable();

        if (isset($this->context)) {
            $this->fp = opendir($path, $this->context);
        } else {
            $this->fp = opendir($path);
        }
        self::enable();

        return $this->fp !== false;
    }

    public function dir_readdir()
    {
        \assert(\is_resource($this->fp));

        return readdir($this->fp);
    }

    public function dir_rewinddir()
    {
        \assert(\is_resource($this->fp));
        rewinddir($this->fp);

        return true;
    }

    public function mkdir($path, $mode, $options)
    {
        self::disable();

        $isRecursive = (bool) ($options & STREAM_MKDIR_RECURSIVE);

        if (isset($this->context)) {
            $return = mkdir($path, $mode, $isRecursive, $this->context);
        } else {
            $return = mkdir($path, $mode, $isRecursive);
        }
        self::enable();

        return $return;
    }

    public function rename($path_from, $path_to)
    {
        self::disable();

        if (isset($this->context)) {
            $return = rename($path_from, $path_to, $this->context);
        } else {
            $return = rename($path_from, $path_to);
        }
        self::enable();

        return $return;
    }

    public function rmdir($path)
    {
        self::disable();

        if (isset($this->context)) {
            $return = rmdir($path, $this->context);
        } else {
            $return = rmdir($path);
        }
        self::enable();

        return $return;
    }

    public function stream_cast()
    {
        return $this->fp;
    }

    public function stream_close()
    {
        \assert(\is_resource($this->fp));

        return fclose($this->fp);
    }

    public function stream_eof()
    {
        \assert(\is_resource($this->fp));

        return feof($this->fp);
    }

    public function stream_flush()
    {
        \assert(\is_resource($this->fp));

        return fflush($this->fp);
    }

    public function stream_lock($operation)
    {
        \assert(\is_resource($this->fp));

        return flock($this->fp, $operation);
    }

    public function stream_metadata($path, $option, $value)
    {
        self::disable();

        switch ($option) {
            case STREAM_META_TOUCH:
                if (empty($value)) {
                    $return = touch($path);
                } else {
                    $return = touch($path, $value[0], $value[1]);
                }

                break;
            case STREAM_META_OWNER_NAME:
            case STREAM_META_OWNER:
                $return = chown($path, $value);

                break;
            case STREAM_META_GROUP_NAME:
            case STREAM_META_GROUP:
                $return = chgrp($path, $value);

                break;
            case STREAM_META_ACCESS:
                $return = chmod($path, $value);

                break;
            default:
                throw new \RuntimeException('Unknown stream_metadata option');
        }
        self::enable();

        return $return;
    }

    public function stream_read($count)
    {
        \assert(\is_resource($this->fp));

        return fread($this->fp, $count);
    }

    public function stream_seek($offset, $whence = SEEK_SET)
    {
        \assert(\is_resource($this->fp));

        return fseek($this->fp, $offset, $whence) === 0;
    }

    public function stream_set_option($option, $arg1, $arg2)
    {
        \assert(\is_resource($this->fp));

        switch ($option) {
            case STREAM_OPTION_BLOCKING:
                stream_set_blocking($this->fp, (bool) $arg1);
                break;
            case STREAM_OPTION_READ_TIMEOUT:
                stream_set_timeout($this->fp, $arg1, $arg2);
                break;
            case STREAM_OPTION_WRITE_BUFFER:
                stream_set_write_buffer($this->fp, $arg1);
                break;
            case STREAM_OPTION_READ_BUFFER:
                stream_set_read_buffer($this->fp, $arg1);
                break;
        }

        return false;
    }

    public function stream_stat()
    {
        \assert(\is_resource($this->fp));

        return fstat($this->fp);
    }

    public function stream_tell()
    {
        \assert(\is_resource($this->fp));

        return ftell($this->fp);
    }

    public function stream_truncate($new_size)
    {
        \assert(\is_resource($this->fp));

        return ftruncate($this->fp, $new_size);
    }

    public function stream_write($data)
    {
        \assert(\is_resource($this->fp));

        return fwrite($this->fp, $data);
    }

    public function unlink($path)
    {
        self::disable();

        if (isset($this->context)) {
            $return = unlink($path, $this->context);
        } else {
            $return = unlink($path);
        }
        self::enable();

        return $return;
    }

    public function url_stat($path)
    {
        self::disable();
        $return = is_readable($path) ? stat($path) : false;
        self::enable();

        return $return;
    }
}
