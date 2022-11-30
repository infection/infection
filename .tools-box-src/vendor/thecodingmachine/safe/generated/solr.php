<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\SolrException;
function solr_get_version() : string
{
    \error_clear_last();
    $safeResult = \solr_get_version();
    if ($safeResult === \false) {
        throw SolrException::createFromPhpError();
    }
    return $safeResult;
}
