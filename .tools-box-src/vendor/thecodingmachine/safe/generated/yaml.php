<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\YamlException;
function yaml_parse_file(string $filename, int $pos = 0, ?int &$ndocs = null, array $callbacks = null)
{
    \error_clear_last();
    if ($callbacks !== null) {
        $safeResult = \yaml_parse_file($filename, $pos, $ndocs, $callbacks);
    } else {
        $safeResult = \yaml_parse_file($filename, $pos, $ndocs);
    }
    if ($safeResult === \false) {
        throw YamlException::createFromPhpError();
    }
    return $safeResult;
}
function yaml_parse_url(string $url, int $pos = 0, ?int &$ndocs = null, array $callbacks = null)
{
    \error_clear_last();
    if ($callbacks !== null) {
        $safeResult = \yaml_parse_url($url, $pos, $ndocs, $callbacks);
    } else {
        $safeResult = \yaml_parse_url($url, $pos, $ndocs);
    }
    if ($safeResult === \false) {
        throw YamlException::createFromPhpError();
    }
    return $safeResult;
}
function yaml_parse(string $input, int $pos = 0, ?int &$ndocs = null, array $callbacks = null)
{
    \error_clear_last();
    if ($callbacks !== null) {
        $safeResult = \yaml_parse($input, $pos, $ndocs, $callbacks);
    } else {
        $safeResult = \yaml_parse($input, $pos, $ndocs);
    }
    if ($safeResult === \false) {
        throw YamlException::createFromPhpError();
    }
    return $safeResult;
}
