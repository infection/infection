<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\LibxmlException;
function libxml_get_last_error() : \LibXMLError
{
    \error_clear_last();
    $safeResult = \libxml_get_last_error();
    if ($safeResult === \false) {
        throw LibxmlException::createFromPhpError();
    }
    return $safeResult;
}
function libxml_set_external_entity_loader(callable $resolver_function) : void
{
    \error_clear_last();
    $safeResult = \libxml_set_external_entity_loader($resolver_function);
    if ($safeResult === \false) {
        throw LibxmlException::createFromPhpError();
    }
}
