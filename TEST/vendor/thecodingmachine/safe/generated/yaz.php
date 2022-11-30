<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\YazException;
function yaz_ccl_parse($id, string $query, ?array &$result) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\yaz_ccl_parse($id, $query, $result);
    if ($result === \false) {
        throw YazException::createFromPhpError();
    }
}
function yaz_close($id) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\yaz_close($id);
    if ($result === \false) {
        throw YazException::createFromPhpError();
    }
}
function yaz_connect(string $zurl, $options = null)
{
    \error_clear_last();
    if ($options !== null) {
        $result = \_HumbugBox9658796bb9f0\yaz_connect($zurl, $options);
    } else {
        $result = \_HumbugBox9658796bb9f0\yaz_connect($zurl);
    }
    if ($result === \false) {
        throw YazException::createFromPhpError();
    }
    return $result;
}
function yaz_database($id, string $databases) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\yaz_database($id, $databases);
    if ($result === \false) {
        throw YazException::createFromPhpError();
    }
}
function yaz_element($id, string $elementset) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\yaz_element($id, $elementset);
    if ($result === \false) {
        throw YazException::createFromPhpError();
    }
}
function yaz_present($id) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\yaz_present($id);
    if ($result === \false) {
        throw YazException::createFromPhpError();
    }
}
function yaz_search($id, string $type, string $query) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\yaz_search($id, $type, $query);
    if ($result === \false) {
        throw YazException::createFromPhpError();
    }
}
function yaz_wait(array &$options = null)
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\yaz_wait($options);
    if ($result === \false) {
        throw YazException::createFromPhpError();
    }
    return $result;
}
