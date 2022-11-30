<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\GnupgException;
function gnupg_adddecryptkey($identifier, string $fingerprint, string $passphrase) : void
{
    \error_clear_last();
    $safeResult = \gnupg_adddecryptkey($identifier, $fingerprint, $passphrase);
    if ($safeResult === \false) {
        throw GnupgException::createFromPhpError();
    }
}
function gnupg_addencryptkey($identifier, string $fingerprint) : void
{
    \error_clear_last();
    $safeResult = \gnupg_addencryptkey($identifier, $fingerprint);
    if ($safeResult === \false) {
        throw GnupgException::createFromPhpError();
    }
}
function gnupg_addsignkey($identifier, string $fingerprint, string $passphrase = null) : void
{
    \error_clear_last();
    if ($passphrase !== null) {
        $safeResult = \gnupg_addsignkey($identifier, $fingerprint, $passphrase);
    } else {
        $safeResult = \gnupg_addsignkey($identifier, $fingerprint);
    }
    if ($safeResult === \false) {
        throw GnupgException::createFromPhpError();
    }
}
function gnupg_cleardecryptkeys($identifier) : void
{
    \error_clear_last();
    $safeResult = \gnupg_cleardecryptkeys($identifier);
    if ($safeResult === \false) {
        throw GnupgException::createFromPhpError();
    }
}
function gnupg_clearencryptkeys($identifier) : void
{
    \error_clear_last();
    $safeResult = \gnupg_clearencryptkeys($identifier);
    if ($safeResult === \false) {
        throw GnupgException::createFromPhpError();
    }
}
function gnupg_clearsignkeys($identifier) : void
{
    \error_clear_last();
    $safeResult = \gnupg_clearsignkeys($identifier);
    if ($safeResult === \false) {
        throw GnupgException::createFromPhpError();
    }
}
function gnupg_deletekey($identifier, string $key, bool $allow_secret) : void
{
    \error_clear_last();
    $safeResult = \gnupg_deletekey($identifier, $key, $allow_secret);
    if ($safeResult === \false) {
        throw GnupgException::createFromPhpError();
    }
}
function gnupg_setarmor($identifier, int $armor) : void
{
    \error_clear_last();
    $safeResult = \gnupg_setarmor($identifier, $armor);
    if ($safeResult === \false) {
        throw GnupgException::createFromPhpError();
    }
}
function gnupg_setsignmode($identifier, int $signmode) : void
{
    \error_clear_last();
    $safeResult = \gnupg_setsignmode($identifier, $signmode);
    if ($safeResult === \false) {
        throw GnupgException::createFromPhpError();
    }
}
