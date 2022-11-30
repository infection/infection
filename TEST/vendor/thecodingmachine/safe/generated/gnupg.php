<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\GnupgException;
function gnupg_adddecryptkey($identifier, string $fingerprint, string $passphrase) : void
{
    \error_clear_last();
    $result = \gnupg_adddecryptkey($identifier, $fingerprint, $passphrase);
    if ($result === \false) {
        throw GnupgException::createFromPhpError();
    }
}
function gnupg_addencryptkey($identifier, string $fingerprint) : void
{
    \error_clear_last();
    $result = \gnupg_addencryptkey($identifier, $fingerprint);
    if ($result === \false) {
        throw GnupgException::createFromPhpError();
    }
}
function gnupg_addsignkey($identifier, string $fingerprint, string $passphrase = null) : void
{
    \error_clear_last();
    if ($passphrase !== null) {
        $result = \gnupg_addsignkey($identifier, $fingerprint, $passphrase);
    } else {
        $result = \gnupg_addsignkey($identifier, $fingerprint);
    }
    if ($result === \false) {
        throw GnupgException::createFromPhpError();
    }
}
function gnupg_cleardecryptkeys($identifier) : void
{
    \error_clear_last();
    $result = \gnupg_cleardecryptkeys($identifier);
    if ($result === \false) {
        throw GnupgException::createFromPhpError();
    }
}
function gnupg_clearencryptkeys($identifier) : void
{
    \error_clear_last();
    $result = \gnupg_clearencryptkeys($identifier);
    if ($result === \false) {
        throw GnupgException::createFromPhpError();
    }
}
function gnupg_clearsignkeys($identifier) : void
{
    \error_clear_last();
    $result = \gnupg_clearsignkeys($identifier);
    if ($result === \false) {
        throw GnupgException::createFromPhpError();
    }
}
function gnupg_setarmor($identifier, int $armor) : void
{
    \error_clear_last();
    $result = \gnupg_setarmor($identifier, $armor);
    if ($result === \false) {
        throw GnupgException::createFromPhpError();
    }
}
function gnupg_setsignmode($identifier, int $signmode) : void
{
    \error_clear_last();
    $result = \gnupg_setsignmode($identifier, $signmode);
    if ($result === \false) {
        throw GnupgException::createFromPhpError();
    }
}
