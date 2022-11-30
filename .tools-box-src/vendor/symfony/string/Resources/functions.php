<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\String;

if (!\function_exists(u::class)) {
    function u(?string $string = '') : UnicodeString
    {
        return new UnicodeString($string ?? '');
    }
}
if (!\function_exists(b::class)) {
    function b(?string $string = '') : ByteString
    {
        return new ByteString($string ?? '');
    }
}
if (!\function_exists(s::class)) {
    function s(?string $string = '') : AbstractString
    {
        $string ??= '';
        return \preg_match('//u', $string) ? new UnicodeString($string) : new ByteString($string);
    }
}
