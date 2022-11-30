<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\RpminfoException;
function rpmaddtag(int $tag) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\rpmaddtag($tag);
    if ($result === \false) {
        throw RpminfoException::createFromPhpError();
    }
}
