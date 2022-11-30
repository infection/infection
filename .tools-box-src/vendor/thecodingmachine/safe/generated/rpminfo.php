<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\RpminfoException;
function rpmaddtag(int $tag) : void
{
    \error_clear_last();
    $safeResult = \_HumbugBoxb47773b41c19\rpmaddtag($tag);
    if ($safeResult === \false) {
        throw RpminfoException::createFromPhpError();
    }
}
