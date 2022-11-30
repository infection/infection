<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\OutcontrolException;
function ob_clean() : void
{
    \error_clear_last();
    $result = \ob_clean();
    if ($result === \false) {
        throw OutcontrolException::createFromPhpError();
    }
}
function ob_end_clean() : void
{
    \error_clear_last();
    $result = \ob_end_clean();
    if ($result === \false) {
        throw OutcontrolException::createFromPhpError();
    }
}
function ob_end_flush() : void
{
    \error_clear_last();
    $result = \ob_end_flush();
    if ($result === \false) {
        throw OutcontrolException::createFromPhpError();
    }
}
function ob_flush() : void
{
    \error_clear_last();
    $result = \ob_flush();
    if ($result === \false) {
        throw OutcontrolException::createFromPhpError();
    }
}
function ob_start($callback = null, int $chunk_size = 0, int $flags = \PHP_OUTPUT_HANDLER_STDFLAGS) : void
{
    \error_clear_last();
    if ($flags !== \PHP_OUTPUT_HANDLER_STDFLAGS) {
        $result = \ob_start($callback, $chunk_size, $flags);
    } elseif ($chunk_size !== 0) {
        $result = \ob_start($callback, $chunk_size);
    } elseif ($callback !== null) {
        $result = \ob_start($callback);
    } else {
        $result = \ob_start();
    }
    if ($result === \false) {
        throw OutcontrolException::createFromPhpError();
    }
}
function output_add_rewrite_var(string $name, string $value) : void
{
    \error_clear_last();
    $result = \output_add_rewrite_var($name, $value);
    if ($result === \false) {
        throw OutcontrolException::createFromPhpError();
    }
}
function output_reset_rewrite_vars() : void
{
    \error_clear_last();
    $result = \output_reset_rewrite_vars();
    if ($result === \false) {
        throw OutcontrolException::createFromPhpError();
    }
}
