<?php

namespace _HumbugBox9658796bb9f0;

if (!\function_exists('_HumbugBox9658796bb9f0\\json5_decode')) {
    function json5_decode($source, $associative = \false, $depth = 512, $options = 0)
    {
        return \_HumbugBox9658796bb9f0\ColinODell\Json5\Json5Decoder::decode($source, $associative, $depth, $options);
    }
}
if (!\defined('JSON_THROW_ON_ERROR')) {
    \define('JSON_THROW_ON_ERROR', 1 << 22);
}
if (!\class_exists('JsonException')) {
    class JsonException extends \Exception
    {
    }
}
