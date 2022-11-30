<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\YamlException;
function yaml_parse_file(string $filename, int $pos = 0, ?int &$ndocs = null, array $callbacks = null)
{
    \error_clear_last();
    if ($callbacks !== null) {
        $result = \yaml_parse_file($filename, $pos, $ndocs, $callbacks);
    } else {
        $result = \yaml_parse_file($filename, $pos, $ndocs);
    }
    if ($result === \false) {
        throw YamlException::createFromPhpError();
    }
    return $result;
}
function yaml_parse_url(string $url, int $pos = 0, ?int &$ndocs = null, array $callbacks = null)
{
    \error_clear_last();
    if ($callbacks !== null) {
        $result = \yaml_parse_url($url, $pos, $ndocs, $callbacks);
    } else {
        $result = \yaml_parse_url($url, $pos, $ndocs);
    }
    if ($result === \false) {
        throw YamlException::createFromPhpError();
    }
    return $result;
}
function yaml_parse(string $input, int $pos = 0, ?int &$ndocs = null, array $callbacks = null)
{
    \error_clear_last();
    if ($callbacks !== null) {
        $result = \yaml_parse($input, $pos, $ndocs, $callbacks);
    } else {
        $result = \yaml_parse($input, $pos, $ndocs);
    }
    if ($result === \false) {
        throw YamlException::createFromPhpError();
    }
    return $result;
}
