<?php


use JetBrains\PhpStorm\Deprecated;

/**
@removed







*/
#[Deprecated(since: "5.5")]
function mcrypt_ecb($cipher, $key, $data, $mode) {}

/**
@removed








*/
#[Deprecated(since: "5.5")]
function mcrypt_cbc($cipher, $key, $data, $mode, $iv = null) {}

/**
@removed








*/
#[Deprecated(since: '5.5')]
function mcrypt_cfb($cipher, $key, $data, $mode, $iv = null) {}

/**
@removed








*/
#[Deprecated(since: '5.5')]
function mcrypt_ofb($cipher, $key, $data, $mode, $iv = null) {}

/**
@removed





*/
#[Deprecated(since: '7.1')]
function mcrypt_get_key_size($cipher, $module) {}

/**
@removed









*/
#[Deprecated(since: '7.1')]
function mcrypt_get_block_size($cipher, $module) {}

/**
@removed








*/
#[Deprecated(since: '7.1')]
function mcrypt_get_cipher_name($cipher) {}

/**
@removed
























*/
#[Deprecated(since: '7.1')]
function mcrypt_create_iv($size, $source = MCRYPT_DEV_URANDOM) {}

/**
@removed








*/
#[Deprecated(since: '7.1')]
function mcrypt_list_algorithms($lib_dir = null) {}

/**
@removed








*/
#[Deprecated(since: '7.1')]
function mcrypt_list_modes($lib_dir = null) {}

/**
@removed
















*/
#[Deprecated(since: '7.1')]
function mcrypt_get_iv_size($cipher, $module) {}

/**
@removed




































*/
#[Deprecated(since: '7.1')]
function mcrypt_encrypt($cipher, $key, $data, $mode, $iv = null) {}

/**
@removed




























*/
#[Deprecated(since: '7.1')]
function mcrypt_decrypt($cipher, $key, $data, $mode, $iv = null) {}

/**
@removed






















*/
#[Deprecated(since: '7.1')]
function mcrypt_module_open($cipher, $cipher_directory, $mode, $mode_directory) {}

/**
@removed
























*/
#[Deprecated(since: '7.1')]
function mcrypt_generic_init($td, $key, $iv) {}

/**
@removed
















*/
#[Deprecated(since: '7.1')]
function mcrypt_generic($td, $data) {}

/**
@removed










*/
#[Deprecated(since: '7.1')]
function mdecrypt_generic($td, $data) {}

/**
@removed




*/
#[Deprecated(since: '5.3')]
function mcrypt_generic_end($td) {}

/**
@removed






*/
#[Deprecated(since: '7.1')]
function mcrypt_generic_deinit($td) {}

/**
@removed






*/
#[Deprecated(since: '7.1')]
function mcrypt_enc_self_test($td) {}

/**
@removed







*/
#[Deprecated(since: '7.1')]
function mcrypt_enc_is_block_algorithm_mode($td) {}

/**
@removed







*/
#[Deprecated(since: '7.1')]
function mcrypt_enc_is_block_algorithm($td) {}

/**
@removed






*/
#[Deprecated(since: '7.1')]
function mcrypt_enc_is_block_mode($td) {}

/**
@removed






*/
#[Deprecated(since: '7.1')]
function mcrypt_enc_get_block_size($td) {}

/**
@removed






*/
#[Deprecated(since: '7.1')]
function mcrypt_enc_get_key_size($td) {}

/**
@removed










*/
#[Deprecated(since: '7.1')]
function mcrypt_enc_get_supported_key_sizes($td) {}

/**
@removed






*/
#[Deprecated(since: '7.1')]
function mcrypt_enc_get_iv_size($td) {}

/**
@removed






*/
#[Deprecated(since: '7.1')]
function mcrypt_enc_get_algorithms_name($td) {}

/**
@removed






*/
#[Deprecated(since: '7.1')]
function mcrypt_enc_get_modes_name($td) {}

/**
@removed











*/
#[Deprecated(since: '7.1')]
function mcrypt_module_self_test($algorithm, $lib_dir = null) {}

/**
@removed












*/
#[Deprecated(since: '7.1')]
function mcrypt_module_is_block_algorithm_mode($mode, $lib_dir = null) {}

/**
@removed











*/
#[Deprecated(since: '7.1')]
function mcrypt_module_is_block_algorithm($algorithm, $lib_dir = null) {}

/**
@removed












*/
#[Deprecated(since: '7.1')]
function mcrypt_module_is_block_mode($mode, $lib_dir = null) {}

/**
@removed










*/
#[Deprecated(since: '7.1')]
function mcrypt_module_get_algo_block_size($algorithm, $lib_dir = null) {}

/**
@removed











*/
#[Deprecated(since: '7.1')]
function mcrypt_module_get_algo_key_size($algorithm, $lib_dir = null) {}

/**
@removed













*/
#[Deprecated(since: '7.1')]
function mcrypt_module_get_supported_key_sizes($algorithm, $lib_dir = null) {}

/**
@removed






*/
#[Deprecated(since: '7.1')]
function mcrypt_module_close($td) {}

/**
@removed

*/
define('MCRYPT_ENCRYPT', 0);
/**
@removed

*/
define('MCRYPT_DECRYPT', 1);
/**
@removed

*/
define('MCRYPT_DEV_RANDOM', 0);
/**
@removed

*/
define('MCRYPT_DEV_URANDOM', 1);
/**
@removed

*/
define('MCRYPT_RAND', 2);
/**
@removed

*/
define('MCRYPT_3DES', "tripledes");
/**
@removed

*/
define('MCRYPT_ARCFOUR_IV', "arcfour-iv");
/**
@removed

*/
define('MCRYPT_ARCFOUR', "arcfour");
/**
@removed

*/
define('MCRYPT_BLOWFISH', "blowfish");
define('MCRYPT_BLOWFISH_COMPAT', "blowfish-compat");
/**
@removed

*/
define('MCRYPT_CAST_128', "cast-128");
/**
@removed

*/
define('MCRYPT_CAST_256', "cast-256");
/**
@removed

*/
define('MCRYPT_CRYPT', "crypt");
/**
@removed

*/
define('MCRYPT_DES', "des");
/**
@removed

*/
define('MCRYPT_DES_COMPAT', "des-compat");
/**
@removed

*/
define('MCRYPT_ENIGNA', "crypt");
/**
@removed

*/
define('MCRYPT_GOST', "gost");
/**
@removed

*/
define('MCRYPT_LOKI97', "loki97");
/**
@removed

*/
define('MCRYPT_PANAMA', "panama");
/**
@removed

*/
define('MCRYPT_RC2', "rc2");
/**
@removed

*/
define('MCRYPT_RC4', "rc4");
/**
@removed

*/
define('MCRYPT_RIJNDAEL_128', "rijndael-128");
/**
@removed

*/
define('MCRYPT_RIJNDAEL_192', "rijndael-192");
/**
@removed

*/
define('MCRYPT_RIJNDAEL_256', "rijndael-256");
/**
@removed

*/
define('MCRYPT_SAFER64', "safer-sk64");
/**
@removed

*/
define('MCRYPT_SAFER128', "safer-sk128");
/**
@removed

*/
define('MCRYPT_SAFERPLUS', "saferplus");
/**
@removed

*/
define('MCRYPT_SERPENT', "serpent");
/**
@removed

*/
define('MCRYPT_SERPENT_128', "serpent-128");
/**
@removed

*/
define('MCRYPT_SERPENT_192', "serpent-192");
/**
@removed

*/
define('MCRYPT_SERPENT_256', "serpent-256");
/**
@removed

*/
define('MCRYPT_THREEWAY', "threeway");
/**
@removed

*/
define('MCRYPT_TRIPLEDES', "tripledes");
/**
@removed

*/
define('MCRYPT_TWOFISH', "twofish");
/**
@removed

*/
define('MCRYPT_WAKE', "wake");
/**
@removed

*/
define('MCRYPT_XTEA', "xtea");
/**
@removed

*/
define('MCRYPT_IDEA', "idea");
/**
@removed

*/
define('MCRYPT_MARS', "mars");
/**
@removed

*/
define('MCRYPT_RC6', "rc6");
/**
@removed

*/
define('MCRYPT_RC6_128', "rc6-128");
/**
@removed

*/
define('MCRYPT_RC6_192', "rc6-192");
/**
@removed

*/
define('MCRYPT_RC6_256', "rc6-256");
/**
@removed

*/
define('MCRYPT_SKIPJACK', "skipjack");
/**
@removed

*/
define('MCRYPT_MODE_CBC', "cbc");
/**
@removed

*/
define('MCRYPT_MODE_CFB', "cfb");
/**
@removed

*/
define('MCRYPT_MODE_ECB', "ecb");
/**
@removed

*/
define('MCRYPT_MODE_NOFB', "nofb");
/**
@removed

*/
define('MCRYPT_MODE_OFB', "ofb");
/**
@removed

*/
define('MCRYPT_MODE_STREAM', "stream");


