<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\GmpException;
function gmp_random_seed($seed) : void
{
    \error_clear_last();
    $safeResult = \gmp_random_seed($seed);
    if ($safeResult === \false) {
        throw GmpException::createFromPhpError();
    }
}
