<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\StreamWrapper;

class IncludeInterceptor
{
    const STREAM_OPEN_FOR_INCLUDE = 0x00000080;

    public $context;

    private $fp;

    private static $intercept;

    private static $replacement;

    public static function intercept($file, $with)
    {
        if (!\file_exists($file)) {
            throw new \InvalidArgumentException(
                'File to intercept and replace does not exist: ' . $file
            );
        }
        if (!\file_exists($with)) {
            throw new \InvalidArgumentException(
                'File to replace intercepted file with does not exist: ' . $file
            );
        }
        self::$intercept = $file;
        self::$replacement = $with;
    }

    public static function enable()
    {
        if (!isset(self::$intercept) || !isset(self::$replacement)) {
            throw new \RuntimeException(
                'Set a file to intercept and its replacement before enabling wrapper'
            );
        }
        \stream_wrapper_unregister('file');
        \stream_wrapper_register('file', __CLASS__);
    }

    public static function disable()
    {
        \stream_wrapper_restore('file');
    }

    public function stream_open($path, $mode, $options)
    {
        self::disable();
        $including = (bool) ($options & self::STREAM_OPEN_FOR_INCLUDE);
        if ($including) {
            if ($path == self::$intercept || \realpath($path) == self::$intercept) {
                $this->fp = \fopen(self::$replacement, 'r');
                self::enable();

                return true;
            }
        }
        if (isset($this->context)) {
            $this->fp = \fopen($path, $mode, $options, $this->context);
        } else {
            $this->fp = \fopen($path, $mode, $options);
        }
        self::enable();

        return $this->fp !== false;
    }

    public function dir_closedir()
    {
        \closedir($this->fp);

        return true;
    }

    public function dir_opendir($path)
    {
        self::disable();
        if (isset($this->context)) {
            $this->fp = \opendir($path, $this->context);
        } else {
            $this->fp = \opendir($path);
        }
        self::enable();

        return $this->fp !== false;
    }

    public function dir_readdir()
    {
        return \readdir($this->fp);
    }

    public function dir_rewinddir()
    {
        \rewinddir($this->fp);

        return true;
    }

    public function mkdir($path, $mode, $options)
    {
        self::disable();

        $isRecursive = (bool) ($options & STREAM_MKDIR_RECURSIVE);

        if (isset($this->context)) {
            $return = \mkdir($path, $mode, $isRecursive, $this->context);
        } else {
            $return = \mkdir($path, $mode, $isRecursive);
        }
        self::enable();

        return $return;
    }

    public function rename($path_from, $path_to)
    {
        self::disable();
        if (isset($this->context)) {
            $return = \rename($path_from, $path_to, $this->context);
        } else {
            $return = \rename($path_from, $path_to);
        }
        self::enable();

        return $return;
    }

    public function rmdir($path)
    {
        self::disable();
        if (isset($this->context)) {
            $return = \rmdir($path, $this->context);
        } else {
            $return = \rmdir($path);
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
        return \fclose($this->fp);
    }

    public function stream_eof()
    {
        return \feof($this->fp);
    }

    public function stream_flush()
    {
        return \fflush($this->fp);
    }

    public function stream_lock($operation)
    {
        return \flock($this->fp, $operation);
    }

    public function stream_metadata($path, $option, $value)
    {
        self::disable();
        switch ($option) {
            case STREAM_META_TOUCH:
                if (empty($value)) {
                    $return = \touch($path);
                } else {
                    $return = \touch($path, $value[0], $value[1]);
                }
                break;
            case STREAM_META_OWNER_NAME:
            case STREAM_META_OWNER:
                $return = \chown($path, $value);
                break;
            case STREAM_META_GROUP_NAME:
            case STREAM_META_GROUP:
                $return = \chgrp($path, $value);
                break;
            case STREAM_META_ACCESS:
                $return = \chmod($path, $value);
                break;
            default:
                throw new \RuntimeException('Unknown stream_metadata option');
        }
        self::enable();

        return $return;
    }

    public function stream_read($count)
    {
        return \fread($this->fp, $count);
    }

    public function stream_seek($offset, $whence = SEEK_SET)
    {
        return \fseek($this->fp, $offset, $whence) === 0;
    }

    public function stream_set_option($option, $arg1, $arg2)
    {
        switch ($option) {
            case STREAM_OPTION_BLOCKING:
                return \stream_set_blocking($this->fp, $arg1);
            case STREAM_OPTION_READ_TIMEOUT:
                return \stream_set_timeout($this->fp, $arg1, $arg2);
            case STREAM_OPTION_WRITE_BUFFER:
                return \stream_set_write_buffer($this->fp, $arg1);
            case STREAM_OPTION_READ_BUFFER:
                return \stream_set_read_buffer($this->fp, $arg1);
        }
    }

    public function stream_stat()
    {
        return \fstat($this->fp);
    }

    public function stream_tell()
    {
        return \ftell($this->fp);
    }

    public function stream_truncate($new_size)
    {
        return \ftruncate($this->fp, $new_size);
    }

    public function stream_write($data)
    {
        return \fwrite($this->fp, $data);
    }

    public function unlink($path)
    {
        self::disable();
        if (isset($this->context)) {
            $return = \unlink($path, $this->context);
        } else {
            $return = \unlink($path);
        }
        self::enable();

        return $return;
    }

    public function url_stat($path)
    {
        self::disable();
        $return = \is_readable($path) ? \stat($path) : false;
        self::enable();

        return $return;
    }
}
