<?php


use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Pure;



















#[Pure]
function hash(string $algo, string $data, bool $binary = false, #[PhpStormStubsElementAvailable('8.1')] array $options = []): string {}









#[Pure]
function hash_equals(string $known_string, string $user_string): bool {}



















#[Pure]
function hash_file(string $algo, string $filename, bool $binary = false, #[PhpStormStubsElementAvailable('8.1')] array $options = []): string|false {}























#[Pure]
function hash_hmac(string $algo, string $data, string $key, bool $binary = false): string {}























#[Pure]
function hash_hmac_file(string $algo, string $filename, string $key, bool $binary = false): string|false {}























#[Pure]
#[LanguageLevelTypeAware(["7.2" => "HashContext"], default: "resource")]
function hash_init(string $algo, int $flags = 0, string $key = "", #[PhpStormStubsElementAvailable('8.1')] array $options = []) {}













function hash_update(#[LanguageLevelTypeAware(["7.2" => "HashContext"], default: "resource")] $context, string $data): bool {}

















function hash_update_stream(#[LanguageLevelTypeAware(["7.2" => "HashContext"], default: "resource")] $context, $stream, int $length = -1): int {}
















function hash_update_file(#[LanguageLevelTypeAware(["7.2" => "HashContext"], default: "resource")] $context, string $filename, $stream_context): bool {}
















function hash_final(#[LanguageLevelTypeAware(["7.2" => "HashContext"], default: "resource")] $context, bool $binary = false): string {}









#[Pure]
#[LanguageLevelTypeAware(["7.2" => "HashContext"], default: "resource")]
function hash_copy(#[LanguageLevelTypeAware(["7.2" => "HashContext"], default: "resource")] $context) {}








#[Pure]
function hash_algos(): array {}





















#[Pure]
#[LanguageLevelTypeAware(["8.0" => "string"], default: "string|false")]
function hash_hkdf(string $algo, string $key, int $length = 0, string $info = '', string $salt = '') {}







#[Pure]
function hash_hmac_algos(): array {}






























#[Pure]
function hash_pbkdf2(string $algo, string $password, string $salt, int $iterations, int $length = 0, bool $binary = false): string {}
























#[Pure]
#[Deprecated(since: '8.1')]
function mhash_keygen_s2k(int $algo, string $password, string $salt, int $length): string|false {}











#[Pure]
#[Deprecated(since: '8.1')]
function mhash_get_block_size(int $algo): int|false {}










#[Pure]
#[Deprecated(since: '8.1')]
function mhash_get_hash_name(int $algo): string|false {}








#[Pure]
#[Deprecated(since: '8.1')]
function mhash_count(): int {}




















#[Pure]
#[Deprecated(since: '8.1')]
function mhash(int $algo, string $data, ?string $key): string|false {}







define('HASH_HMAC', 1);
define('MHASH_CRC32', 0);



define('MHASH_CRC32C', 34);
define('MHASH_MD5', 1);
define('MHASH_SHA1', 2);
define('MHASH_HAVAL256', 3);
define('MHASH_RIPEMD160', 5);
define('MHASH_TIGER', 7);
define('MHASH_GOST', 8);
define('MHASH_CRC32B', 9);
define('MHASH_HAVAL224', 10);
define('MHASH_HAVAL192', 11);
define('MHASH_HAVAL160', 12);
define('MHASH_HAVAL128', 13);
define('MHASH_TIGER128', 14);
define('MHASH_TIGER160', 15);
define('MHASH_MD4', 16);
define('MHASH_SHA256', 17);
define('MHASH_ADLER32', 18);
define('MHASH_SHA224', 19);
define('MHASH_SHA512', 20);
define('MHASH_SHA384', 21);
define('MHASH_WHIRLPOOL', 22);
define('MHASH_RIPEMD128', 23);
define('MHASH_RIPEMD256', 24);
define('MHASH_RIPEMD320', 25);
define('MHASH_SNEFRU256', 27);
define('MHASH_MD2', 28);
define('MHASH_FNV132', 29);
define('MHASH_FNV1A32', 30);
define('MHASH_FNV164', 31);
define('MHASH_FNV1A64', 32);
define('MHASH_JOAAT', 33);



define('MHASH_MURMUR3A', 35);



define('MHASH_MURMUR3C', 36);



define('MHASH_MURMUR3F', 37);



define('MHASH_XXH32', 38);



define('MHASH_XXH64', 39);



define('MHASH_XXH3', 40);



define('MHASH_XXH128', 41);




final class HashContext
{
private function __construct() {}

public function __serialize(): array {}




public function __unserialize(#[LanguageLevelTypeAware(['8.0' => 'array'], default: '')] $data): void {}
}

