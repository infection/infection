<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\InotifyException;
function inotify_init()
{
    \error_clear_last();
    $safeResult = \inotify_init();
    if ($safeResult === \false) {
        throw InotifyException::createFromPhpError();
    }
    return $safeResult;
}
function inotify_rm_watch($inotify_instance, int $watch_descriptor) : void
{
    \error_clear_last();
    $safeResult = \inotify_rm_watch($inotify_instance, $watch_descriptor);
    if ($safeResult === \false) {
        throw InotifyException::createFromPhpError();
    }
}
