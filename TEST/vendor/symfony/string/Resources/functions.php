<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\String;

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
        $string = $string ?? '';
        return \preg_match('//u', $string) ? new UnicodeString($string) : new ByteString($string);
    }
}
