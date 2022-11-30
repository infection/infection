<?php

use JetBrains\PhpStorm\ExpectedValues;




define('GNUPG_SIG_MODE_NORMAL', 0);
define('GNUPG_SIG_MODE_DETACH', 1);
define('GNUPG_SIG_MODE_CLEAR', 2);
define('GNUPG_VALIDITY_UNKNOWN', 0);
define('GNUPG_VALIDITY_UNDEFINED', 1);
define('GNUPG_VALIDITY_NEVER', 2);
define('GNUPG_VALIDITY_MARGINAL', 3);
define('GNUPG_VALIDITY_FULL', 4);
define('GNUPG_VALIDITY_ULTIMATE', 5);
define('GNUPG_PROTOCOL_OpenPGP', 0);
define('GNUPG_PROTOCOL_CMS', 1);
define('GNUPG_SIGSUM_VALID', 1);
define('GNUPG_SIGSUM_GREEN', 2);
define('GNUPG_SIGSUM_RED', 4);
define('GNUPG_SIGSUM_KEY_REVOKED', 16);
define('GNUPG_SIGSUM_KEY_EXPIRED', 32);
define('GNUPG_SIGSUM_SIG_EXPIRED', 64);
define('GNUPG_SIGSUM_KEY_MISSING', 128);
define('GNUPG_SIGSUM_CRL_MISSING', 256);
define('GNUPG_SIGSUM_CRL_TOO_OLD', 512);
define('GNUPG_SIGSUM_BAD_POLICY', 1024);
define('GNUPG_SIGSUM_SYS_ERROR', 2048);
define('GNUPG_ERROR_WARNING', 1);
define('GNUPG_ERROR_EXCEPTION', 2);
define('GNUPG_ERROR_SILENT', 3);
define('GNUPG_PK_RSA', 1);
define('GNUPG_PK_RSA_E', 2);
define('GNUPG_PK_RSA_S', 3);
define('GNUPG_PK_DSA', 17);
define('GNUPG_PK_ELG', 20);
define('GNUPG_PK_ELG_E', 16);
define('GNUPG_PK_ECC', 18);
define('GNUPG_PK_ECDSA', 301);
define('GNUPG_PK_ECDH', 302);
define('GNUPG_PK_EDDSA', 303);
define('GNUPG_GPGME_VERSION', '1.15.1');






class gnupg
{
public const SIG_MODE_NORMAL = 0;
public const SIG_MODE_DETACH = 1;
public const SIG_MODE_CLEAR = 2;
public const VALIDITY_UNKNOWN = 0;
public const VALIDITY_UNDEFINED = 1;
public const VALIDITY_NEVER = 2;
public const VALIDITY_MARGINAL = 3;
public const VALIDITY_FULL = 4;
public const VALIDITY_ULTIMATE = 5;
public const PROTOCOL_OpenPGP = 0;
public const PROTOCOL_CMS = 1;
public const SIGSUM_VALID = 1;
public const SIGSUM_GREEN = 2;
public const SIGSUM_RED = 4;
public const SIGSUM_KEY_REVOKED = 16;
public const SIGSUM_KEY_EXPIRED = 32;
public const SIGSUM_SIG_EXPIRED = 64;
public const SIGSUM_KEY_MISSING = 128;
public const SIGSUM_CRL_MISSING = 256;
public const SIGSUM_CRL_TOO_OLD = 512;
public const SIGSUM_BAD_POLICY = 1024;
public const SIGSUM_SYS_ERROR = 2048;
public const ERROR_WARNING = 1;
public const ERROR_EXCEPTION = 2;
public const ERROR_SILENT = 3;
public const PK_RSA = 1;
public const PK_RSA_E = 2;
public const PK_RSA_S = 3;
public const PK_DSA = 17;
public const PK_ELG = 20;
public const PK_ELG_E = 16;
public const PK_ECC = 18;
public const PK_ECDSA = 301;
public const PK_ECDH = 302;
public const PK_EDDSA = 303;

public function __construct($options = null) {}










public function adddecryptkey($kye, $passphrase) {}












public function verify($text, $signature, &$plaintext = null) {}









public function addencryptkey($kye) {}










public function addsignkey($kye, $passphrase = null) {}

public function deletekey($kye, $allow_secret) {}

public function gettrustlist($pattern) {}

public function listsignatures($kyeid) {}







public function cleardecryptkeys() {}







public function clearencryptkeys() {}







public function clearsignkeys() {}










public function decrypt($enctext) {}












public function decryptverify($enctext, &$plaintext) {}










public function encrypt($text) {}










public function encryptsign($text) {}










public function export($pattern) {}







public function geterror() {}









public function getprotocol() {}










public function import($kye) {}







public function init() {}










public function keyinfo($pattern, $secret_only = false) {}









public function setarmor($armor) {}









public function seterrormode($errnmode) {}









public function setsignmode($signmode) {}










public function sign($text) {}

public function getengineinfo() {}

public function geterrorinfo() {}
}

class gnupg_keylistiterator implements Iterator
{
public function __construct() {}

public function current() {}

public function key() {}

public function next() {}

public function rewind() {}

public function valid() {}
}







function gnupg_init($options = null) {}









function gnupg_keyinfo($res, $pattern, $secret_only = false) {}









function gnupg_sign($res, $text) {}







function gnupg_clearsignkeys($res) {}











function gnupg_verify($res, $text, $signature, &$plaintext = '') {}







function gnupg_clearencryptkeys($res) {}







function gnupg_cleardecryptkeys($res) {}









function gnupg_adddecryptkey($res, $kye, $passphrase) {}








function gnupg_addencryptkey($res, $kye) {}









function gnupg_setarmor($res, $armor) {}








function gnupg_encrypt($res, $text) {}








function gnupg_decrypt($res, $enctext) {}








function gnupg_export($res, $pattern) {}









function gnupg_import($res, $kye) {}

function gnupg_getengineinfo($res) {}







function gnupg_getprotocol($res) {}










function gnupg_setsignmode($res, #[ExpectedValues([GNUPG_SIG_MODE_NORMAL|GNUPG_SIG_MODE_DETACH|GNUPG_SIG_MODE_CLEAR])] $signmode) {}









function gnupg_encryptsign($res, $text) {}










function gnupg_decryptverify($res, $enctext, &$plaintext) {}







function gnupg_geterror($res) {}

function gnupg_geterrorinfo($res) {}









function gnupg_addsignkey($res, $kye, $passphrase) {}
function gnupg_deletekey($res, $kye, $allow_secret) {}
function gnupg_gettrustlist($res, $pattern) {}
function gnupg_listsignatures($res, $kyeid) {}








function gnupg_seterrormode($res, #[ExpectedValues([GNUPG_ERROR_WARNING|GNUPG_ERROR_EXCEPTION|GNUPG_ERROR_SILENT])] $errnmode) {}
