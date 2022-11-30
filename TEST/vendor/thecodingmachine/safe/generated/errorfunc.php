<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\ErrorfuncException;
function error_log(string $message, int $message_type = 0, string $destination = null, string $additional_headers = null) : void
{
    \error_clear_last();
    if ($additional_headers !== null) {
        $result = \error_log($message, $message_type, $destination, $additional_headers);
    } elseif ($destination !== null) {
        $result = \error_log($message, $message_type, $destination);
    } else {
        $result = \error_log($message, $message_type);
    }
    if ($result === \false) {
        throw ErrorfuncException::createFromPhpError();
    }
}
