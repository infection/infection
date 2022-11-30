<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\ExecException;
function exec(string $command, ?array &$output = null, ?int &$result_code = null) : string
{
    \error_clear_last();
    $safeResult = \exec($command, $output, $result_code);
    if ($safeResult === \false) {
        throw ExecException::createFromPhpError();
    }
    return $safeResult;
}
function passthru(string $command, ?int &$result_code = null) : void
{
    \error_clear_last();
    $safeResult = \passthru($command, $result_code);
    if ($safeResult === \false) {
        throw ExecException::createFromPhpError();
    }
}
function proc_nice(int $priority) : void
{
    \error_clear_last();
    $safeResult = \proc_nice($priority);
    if ($safeResult === \false) {
        throw ExecException::createFromPhpError();
    }
}
function shell_exec(string $command) : string
{
    \error_clear_last();
    $safeResult = \shell_exec($command);
    if ($safeResult === null) {
        throw ExecException::createFromPhpError();
    }
    return $safeResult;
}
function system(string $command, ?int &$result_code = null) : string
{
    \error_clear_last();
    $safeResult = \system($command, $result_code);
    if ($safeResult === \false) {
        throw ExecException::createFromPhpError();
    }
    return $safeResult;
}
