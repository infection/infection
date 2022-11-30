<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\ExecException;
function exec(string $command, ?array &$output = null, ?int &$result_code = null) : string
{
    \error_clear_last();
    $result = \exec($command, $output, $result_code);
    if ($result === \false) {
        throw ExecException::createFromPhpError();
    }
    return $result;
}
function proc_nice(int $priority) : void
{
    \error_clear_last();
    $result = \proc_nice($priority);
    if ($result === \false) {
        throw ExecException::createFromPhpError();
    }
}
function shell_exec(string $command) : string
{
    \error_clear_last();
    $result = \shell_exec($command);
    if ($result === null) {
        throw ExecException::createFromPhpError();
    }
    return $result;
}
function system(string $command, ?int &$result_code = null) : string
{
    \error_clear_last();
    $result = \system($command, $result_code);
    if ($result === \false) {
        throw ExecException::createFromPhpError();
    }
    return $result;
}
