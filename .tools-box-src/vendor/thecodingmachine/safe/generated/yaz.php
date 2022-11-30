<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\YazException;
function yaz_ccl_parse($id, string $query, ?array &$result) : void
{
    \error_clear_last();
    $safeResult = \_HumbugBoxb47773b41c19\yaz_ccl_parse($id, $query, $result);
    if ($safeResult === \false) {
        throw YazException::createFromPhpError();
    }
}
function yaz_close($id) : void
{
    \error_clear_last();
    $safeResult = \_HumbugBoxb47773b41c19\yaz_close($id);
    if ($safeResult === \false) {
        throw YazException::createFromPhpError();
    }
}
function yaz_connect(string $zurl, $options = null)
{
    \error_clear_last();
    if ($options !== null) {
        $safeResult = \_HumbugBoxb47773b41c19\yaz_connect($zurl, $options);
    } else {
        $safeResult = \_HumbugBoxb47773b41c19\yaz_connect($zurl);
    }
    if ($safeResult === \false) {
        throw YazException::createFromPhpError();
    }
    return $safeResult;
}
function yaz_database($id, string $databases) : void
{
    \error_clear_last();
    $safeResult = \_HumbugBoxb47773b41c19\yaz_database($id, $databases);
    if ($safeResult === \false) {
        throw YazException::createFromPhpError();
    }
}
function yaz_element($id, string $elementset) : void
{
    \error_clear_last();
    $safeResult = \_HumbugBoxb47773b41c19\yaz_element($id, $elementset);
    if ($safeResult === \false) {
        throw YazException::createFromPhpError();
    }
}
function yaz_present($id) : void
{
    \error_clear_last();
    $safeResult = \_HumbugBoxb47773b41c19\yaz_present($id);
    if ($safeResult === \false) {
        throw YazException::createFromPhpError();
    }
}
function yaz_search($id, string $type, string $query) : void
{
    \error_clear_last();
    $safeResult = \_HumbugBoxb47773b41c19\yaz_search($id, $type, $query);
    if ($safeResult === \false) {
        throw YazException::createFromPhpError();
    }
}
function yaz_wait(array &$options = null)
{
    \error_clear_last();
    $safeResult = \_HumbugBoxb47773b41c19\yaz_wait($options);
    if ($safeResult === \false) {
        throw YazException::createFromPhpError();
    }
    return $safeResult;
}
