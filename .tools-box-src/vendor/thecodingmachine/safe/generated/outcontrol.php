<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\OutcontrolException;
function ob_clean() : void
{
    \error_clear_last();
    $safeResult = \ob_clean();
    if ($safeResult === \false) {
        throw OutcontrolException::createFromPhpError();
    }
}
function ob_end_clean() : void
{
    \error_clear_last();
    $safeResult = \ob_end_clean();
    if ($safeResult === \false) {
        throw OutcontrolException::createFromPhpError();
    }
}
function ob_end_flush() : void
{
    \error_clear_last();
    $safeResult = \ob_end_flush();
    if ($safeResult === \false) {
        throw OutcontrolException::createFromPhpError();
    }
}
function ob_flush() : void
{
    \error_clear_last();
    $safeResult = \ob_flush();
    if ($safeResult === \false) {
        throw OutcontrolException::createFromPhpError();
    }
}
function ob_start($callback = null, int $chunk_size = 0, int $flags = \PHP_OUTPUT_HANDLER_STDFLAGS) : void
{
    \error_clear_last();
    if ($flags !== \PHP_OUTPUT_HANDLER_STDFLAGS) {
        $safeResult = \ob_start($callback, $chunk_size, $flags);
    } elseif ($chunk_size !== 0) {
        $safeResult = \ob_start($callback, $chunk_size);
    } elseif ($callback !== null) {
        $safeResult = \ob_start($callback);
    } else {
        $safeResult = \ob_start();
    }
    if ($safeResult === \false) {
        throw OutcontrolException::createFromPhpError();
    }
}
function output_add_rewrite_var(string $name, string $value) : void
{
    \error_clear_last();
    $safeResult = \output_add_rewrite_var($name, $value);
    if ($safeResult === \false) {
        throw OutcontrolException::createFromPhpError();
    }
}
function output_reset_rewrite_vars() : void
{
    \error_clear_last();
    $safeResult = \output_reset_rewrite_vars();
    if ($safeResult === \false) {
        throw OutcontrolException::createFromPhpError();
    }
}
