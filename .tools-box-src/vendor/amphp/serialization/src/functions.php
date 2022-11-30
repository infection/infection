<?php

namespace _HumbugBoxb47773b41c19\Amp\Serialization;

function encodeUnprintableChars(string $data) : string
{
    return \preg_replace_callback("/[^ -~]/", function (array $matches) : string {
        return "\\x" . \dechex(\ord($matches[0]));
    }, $data);
}
