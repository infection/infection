<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\LibxmlException;
function libxml_get_last_error() : \LibXMLError
{
    \error_clear_last();
    $result = \libxml_get_last_error();
    if ($result === \false) {
        throw LibxmlException::createFromPhpError();
    }
    return $result;
}
function libxml_set_external_entity_loader(callable $resolver_function) : void
{
    \error_clear_last();
    $result = \libxml_set_external_entity_loader($resolver_function);
    if ($result === \false) {
        throw LibxmlException::createFromPhpError();
    }
}
