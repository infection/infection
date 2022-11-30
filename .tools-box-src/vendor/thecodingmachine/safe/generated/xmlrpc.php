<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\XmlrpcException;
function xmlrpc_set_type(&$value, string $type) : void
{
    \error_clear_last();
    $safeResult = \xmlrpc_set_type($value, $type);
    if ($safeResult === \false) {
        throw XmlrpcException::createFromPhpError();
    }
}
