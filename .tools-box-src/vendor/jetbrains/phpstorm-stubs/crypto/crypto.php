<?php

namespace Crypto;




class Cipher
{
public const MODE_ECB = 1;
public const MODE_CBC = 2;
public const MODE_CFB = 3;
public const MODE_OFB = 4;
public const MODE_CTR = 5;
public const MODE_GCM = 6;
public const MODE_CCM = 7;
public const MODE_XTS = 65537;







public static function getAlgorithms($aliases = false, $prefix = null) {}






public static function hasAlgorithm($algorithm) {}






public static function hasMode($mode) {}






public static function __callStatic($name, $arguments) {}







public function __construct($algorithm, $mode = null, $key_size = null) {}





public function getAlgorithmName() {}







public function encryptInit($key, $iv = null) {}






public function encryptUpdate($data) {}





public function encryptFinish() {}








public function encrypt($data, $key, $iv = null) {}







public function decryptInit($key, $iv = null) {}






public function decryptUpdate($data) {}





public function decryptFinish() {}








public function decrypt($data, $key, $iv = null) {}





public function getBlockSize() {}





public function getKeyLength() {}





public function getIVLength() {}





public function getMode() {}





public function getTag() {}






public function setTag($tag) {}






public function setTagLength($tag_length) {}






public function setAAD($aad) {}
}




class CipherException extends \Exception
{



public const ALGORITHM_NOT_FOUND = 1;




public const STATIC_METHOD_NOT_FOUND = 2;




public const STATIC_METHOD_TOO_MANY_ARGS = 3;




public const MODE_NOT_FOUND = 4;




public const MODE_NOT_AVAILABLE = 5;




public const AUTHENTICATION_NOT_SUPPORTED = 6;




public const KEY_LENGTH_INVALID = 7;




public const IV_LENGTH_INVALID = 8;




public const AAD_SETTER_FORBIDDEN = 9;




public const AAD_SETTER_FAILED = 10;




public const AAD_LENGTH_HIGH = 11;




public const TAG_GETTER_FORBIDDEN = 12;




public const TAG_SETTER_FORBIDDEN = 13;




public const TAG_GETTER_FAILED = 14;




public const TAG_SETTER_FAILED = 15;




public const TAG_LENGTH_SETTER_FORBIDDEN = 16;




public const TAG_LENGTH_LOW = 17;




public const TAG_LENGTH_HIGH = 18;




public const TAG_VERIFY_FAILED = 19;




public const INIT_ALG_FAILED = 20;




public const INIT_CTX_FAILED = 21;




public const INIT_ENCRYPT_FORBIDDEN = 22;




public const INIT_DECRYPT_FORBIDDEN = 23;




public const UPDATE_FAILED = 24;




public const UPDATE_ENCRYPT_FORBIDDEN = 25;




public const UPDATE_DECRYPT_FORBIDDEN = 26;




public const FINISH_FAILED = 27;




public const FINISH_ENCRYPT_FORBIDDEN = 28;




public const FINISH_DECRYPT_FORBIDDEN = 29;




public const INPUT_DATA_LENGTH_HIGH = 30;
}




class Hash
{






public static function getAlgorithms($aliases = false, $prefix = null) {}






public static function hasAlgorithm($algorithm) {}






public static function __callStatic($name, $arguments) {}





public function __construct($algorithm) {}





public function getAlgorithmName() {}






public function update($data) {}





public function digest() {}





public function hexdigest() {}





public function getBlockSize() {}





public function getSize() {}
}




class HashException extends \Exception
{



public const HASH_ALGORITHM_NOT_FOUND = 1;




public const STATIC_METHOD_NOT_FOUND = 2;




public const STATIC_METHOD_TOO_MANY_ARGS = 3;




public const INIT_FAILED = 4;




public const UPDATE_FAILED = 5;




public const DIGEST_FAILED = 6;




public const INPUT_DATA_LENGTH_HIGH = 7;
}




abstract class MAC extends Hash
{





public function __construct($algorithm, $key) {}
}




class MACException extends HashException
{



public const MAC_ALGORITHM_NOT_FOUND = 1;




public const KEY_LENGTH_INVALID = 2;
}




class HMAC extends MAC {}




class CMAC extends MAC {}




abstract class KDF
{





public function __construct($length, $salt = null) {}





public function getLength() {}






public function setLength($length) {}





public function getSalt() {}






public function setSalt($salt) {}
}




class KDFException
{



public const KEY_LENGTH_LOW = 1;




public const KEY_LENGTH_HIGH = 2;




public const SALT_LENGTH_HIGH = 3;




public const PASSWORD_LENGTH_INVALID = 4;




public const DERIVATION_FAILED = 5;
}




class PBKDF2 extends KDF
{







public function __construct($hashAlgorithm, $length, $salt = null, $iterations = 1000) {}






public function derive($password) {}





public function getIterations() {}






public function setIterations($iterations) {}





public function getHashAlgorithm() {}






public function setHashAlgorithm($hashAlgorithm) {}
}




class PBKDF2Exception extends KDFException
{



public const HASH_ALGORITHM_NOT_FOUND = 1;




public const ITERATIONS_HIGH = 2;
}




class Base64
{





public function encode($data) {}






public function decode($data) {}




public function __construct() {}






public function encodeUpdate($data) {}




public function encodeFinish() {}






public function decodeUpdate($data) {}




public function decodeFinish() {}
}




class Base64Exception extends \Exception
{



public const ENCODE_UPDATE_FORBIDDEN = 1;




public const ENCODE_FINISH_FORBIDDEN = 2;




public const DECODE_UPDATE_FORBIDDEN = 3;




public const DECODE_FINISH_FORBIDDEN = 4;




public const DECODE_UPDATE_FAILED = 5;




public const INPUT_DATA_LENGTH_HIGH = 6;
}




class Rand
{







public static function generate($num, $must_be_strong = true, &$returned_strong_result = true) {}







public static function seed($buf, $entropy) {}





public static function cleanup() {}









public static function loadFile($filename, $max_bytes = -1) {}







public static function writeFile($filename) {}
}




class RandException extends \Exception
{



public const GENERATE_PREDICTABLE = 1;




public const FILE_WRITE_PREDICTABLE = 2;




public const REQUESTED_BYTES_NUMBER_TOO_HIGH = 3;




public const SEED_LENGTH_TOO_HIGH = 4;
}
