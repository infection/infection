<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\FilterException;
function filter_input_array(int $type, $options = \FILTER_DEFAULT, bool $add_empty = \true) : ?array
{
    \error_clear_last();
    $safeResult = \filter_input_array($type, $options, $add_empty);
    if ($safeResult === \false) {
        throw FilterException::createFromPhpError();
    }
    return $safeResult;
}
function filter_var_array(array $array, $options = \FILTER_DEFAULT, bool $add_empty = \true) : ?array
{
    \error_clear_last();
    $safeResult = \filter_var_array($array, $options, $add_empty);
    if ($safeResult === \false) {
        throw FilterException::createFromPhpError();
    }
    return $safeResult;
}
