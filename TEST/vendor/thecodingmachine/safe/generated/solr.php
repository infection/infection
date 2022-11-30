<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\SolrException;
function solr_get_version() : string
{
    \error_clear_last();
    $result = \solr_get_version();
    if ($result === \false) {
        throw SolrException::createFromPhpError();
    }
    return $result;
}
