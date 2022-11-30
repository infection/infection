<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\FilterException;
function filter_input_array(int $type, $options = \FILTER_DEFAULT, bool $add_empty = \true) : ?array
{
    \error_clear_last();
    $result = \filter_input_array($type, $options, $add_empty);
    if ($result === \false) {
        throw FilterException::createFromPhpError();
    }
    return $result;
}
function filter_var_array(array $array, $options = \FILTER_DEFAULT, bool $add_empty = \true) : ?array
{
    \error_clear_last();
    $result = \filter_var_array($array, $options, $add_empty);
    if ($result === \false) {
        throw FilterException::createFromPhpError();
    }
    return $result;
}
