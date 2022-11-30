<?php

namespace _HumbugBoxb47773b41c19;

use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\VarDumper;
if (!\function_exists('_HumbugBoxb47773b41c19\\dump')) {
    function dump(mixed $var, mixed ...$moreVars) : mixed
    {
        VarDumper::dump($var);
        foreach ($moreVars as $v) {
            VarDumper::dump($v);
        }
        if (1 < \func_num_args()) {
            return \func_get_args();
        }
        return $var;
    }
}
if (!\function_exists('_HumbugBoxb47773b41c19\\dd')) {
    function dd(...$vars) : void
    {
        if (!\in_array(\PHP_SAPI, ['cli', 'phpdbg'], \true) && !\headers_sent()) {
            \header('HTTP/1.1 500 Internal Server Error');
        }
        foreach ($vars as $v) {
            VarDumper::dump($v);
        }
        exit(1);
    }
}
