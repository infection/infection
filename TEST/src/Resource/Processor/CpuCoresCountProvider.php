<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Resource\Processor;

use function defined;
use function extension_loaded;
use const FILTER_VALIDATE_INT;
use function filter_var;
use function function_exists;
use function is_int;
use function is_readable;
use _HumbugBox9658796bb9f0\Safe\Exceptions\ExecException;
use function _HumbugBox9658796bb9f0\Safe\file_get_contents;
use function _HumbugBox9658796bb9f0\Safe\shell_exec;
use function substr_count;
use function trim;
final class CpuCoresCountProvider
{
    public static function provide() : int
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            return 1;
        }
        if (!extension_loaded('pcntl') || !function_exists('shell_exec')) {
            return 1;
        }
        try {
            $hasNproc = trim(@shell_exec('command -v nproc'));
            if ($hasNproc !== '') {
                $nproc = trim(shell_exec('nproc'));
                $cpuCount = filter_var($nproc, FILTER_VALIDATE_INT);
                if (is_int($cpuCount)) {
                    return $cpuCount;
                }
            }
        } catch (ExecException) {
        }
        try {
            $ncpu = trim(shell_exec('sysctl -n hw.ncpu'));
            $cpuCount = filter_var($ncpu, FILTER_VALIDATE_INT);
            if (is_int($cpuCount)) {
                return $cpuCount;
            }
        } catch (ExecException) {
        }
        if (is_readable('/proc/cpuinfo')) {
            $cpuInfo = file_get_contents('/proc/cpuinfo');
            $cpuCount = substr_count($cpuInfo, 'processor');
            if ($cpuCount > 0) {
                return $cpuCount;
            }
        }
        return 1;
    }
}
