<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\GmpException;
function gmp_random_seed($seed) : void
{
    \error_clear_last();
    $result = \gmp_random_seed($seed);
    if ($result === \false) {
        throw GmpException::createFromPhpError();
    }
}
