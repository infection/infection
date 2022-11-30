<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\ReadlineException;
function readline_add_history(string $prompt) : void
{
    \error_clear_last();
    $safeResult = \readline_add_history($prompt);
    if ($safeResult === \false) {
        throw ReadlineException::createFromPhpError();
    }
}
function readline_callback_handler_install(string $prompt, callable $callback) : void
{
    \error_clear_last();
    $safeResult = \readline_callback_handler_install($prompt, $callback);
    if ($safeResult === \false) {
        throw ReadlineException::createFromPhpError();
    }
}
function readline_clear_history() : void
{
    \error_clear_last();
    $safeResult = \readline_clear_history();
    if ($safeResult === \false) {
        throw ReadlineException::createFromPhpError();
    }
}
function readline_completion_function(callable $callback) : void
{
    \error_clear_last();
    $safeResult = \readline_completion_function($callback);
    if ($safeResult === \false) {
        throw ReadlineException::createFromPhpError();
    }
}
function readline_read_history(string $filename = null) : void
{
    \error_clear_last();
    if ($filename !== null) {
        $safeResult = \readline_read_history($filename);
    } else {
        $safeResult = \readline_read_history();
    }
    if ($safeResult === \false) {
        throw ReadlineException::createFromPhpError();
    }
}
function readline_write_history(string $filename = null) : void
{
    \error_clear_last();
    if ($filename !== null) {
        $safeResult = \readline_write_history($filename);
    } else {
        $safeResult = \readline_write_history();
    }
    if ($safeResult === \false) {
        throw ReadlineException::createFromPhpError();
    }
}
