<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\ErrorfuncException;
function error_log(string $message, int $message_type = 0, string $destination = null, string $additional_headers = null) : void
{
    \error_clear_last();
    if ($additional_headers !== null) {
        $safeResult = \error_log($message, $message_type, $destination, $additional_headers);
    } elseif ($destination !== null) {
        $safeResult = \error_log($message, $message_type, $destination);
    } else {
        $safeResult = \error_log($message, $message_type);
    }
    if ($safeResult === \false) {
        throw ErrorfuncException::createFromPhpError();
    }
}
