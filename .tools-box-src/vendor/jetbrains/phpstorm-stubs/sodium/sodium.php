<?php

use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;

const SODIUM_CRYPTO_AEAD_AES256GCM_KEYBYTES = 32;
const SODIUM_CRYPTO_AEAD_AES256GCM_NSECBYTES = 0;
const SODIUM_CRYPTO_AEAD_AES256GCM_NPUBBYTES = 12;
const SODIUM_CRYPTO_AEAD_AES256GCM_ABYTES = 16;
const SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_KEYBYTES = 32;
const SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_NSECBYTES = 0;
const SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_NPUBBYTES = 8;
const SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_ABYTES = 16;
const SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_KEYBYTES = 32;
const SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NSECBYTES = 0;
const SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES = 12;
const SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_ABYTES = 16;
const SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES = 32;
const SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NSECBYTES = 0;
const SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES = 24;
const SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_ABYTES = 16;
const SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES = 17;
const SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES = 24;
const SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_KEYBYTES = 32;
const SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_MESSAGEBYTES_MAX = 274877906816;
const SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_MESSAGE = 0;
const SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_PUSH = 1;
const SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_REKEY = 2;
const SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL = 3;
const SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13 = 2;
const SODIUM_BASE64_VARIANT_ORIGINAL = 1;
const SODIUM_BASE64_VARIANT_ORIGINAL_NO_PADDING = 3;
const SODIUM_BASE64_VARIANT_URLSAFE = 5;
const SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING = 7;
const SODIUM_CRYPTO_AUTH_BYTES = 32;
const SODIUM_CRYPTO_AUTH_KEYBYTES = 32;
const SODIUM_CRYPTO_BOX_SEALBYTES = 48;
const SODIUM_CRYPTO_BOX_SECRETKEYBYTES = 32;
const SODIUM_CRYPTO_BOX_PUBLICKEYBYTES = 32;
const SODIUM_CRYPTO_BOX_KEYPAIRBYTES = 64;
const SODIUM_CRYPTO_BOX_MACBYTES = 16;
const SODIUM_CRYPTO_BOX_NONCEBYTES = 24;
const SODIUM_CRYPTO_BOX_SEEDBYTES = 32;
const SODIUM_CRYPTO_KX_BYTES = 32;
const SODIUM_CRYPTO_KX_PUBLICKEYBYTES = 32;
const SODIUM_CRYPTO_KX_SECRETKEYBYTES = 32;
const SODIUM_CRYPTO_GENERICHASH_BYTES = 32;
const SODIUM_CRYPTO_GENERICHASH_BYTES_MIN = 16;
const SODIUM_CRYPTO_GENERICHASH_BYTES_MAX = 64;
const SODIUM_CRYPTO_GENERICHASH_KEYBYTES = 32;
const SODIUM_CRYPTO_GENERICHASH_KEYBYTES_MIN = 16;
const SODIUM_CRYPTO_GENERICHASH_KEYBYTES_MAX = 64;
const SODIUM_CRYPTO_PWHASH_SCRYPTSALSA208SHA256_SALTBYTES = 32;
const SODIUM_CRYPTO_PWHASH_SCRYPTSALSA208SHA256_STRPREFIX = '$7$';
const SODIUM_CRYPTO_PWHASH_SCRYPTSALSA208SHA256_OPSLIMIT_INTERACTIVE = 524288;
const SODIUM_CRYPTO_PWHASH_SCRYPTSALSA208SHA256_MEMLIMIT_INTERACTIVE = 16777216;
const SODIUM_CRYPTO_PWHASH_SCRYPTSALSA208SHA256_OPSLIMIT_SENSITIVE = 33554432;
const SODIUM_CRYPTO_PWHASH_SCRYPTSALSA208SHA256_MEMLIMIT_SENSITIVE = 1073741824;
const SODIUM_CRYPTO_SCALARMULT_BYTES = 32;
const SODIUM_CRYPTO_SCALARMULT_SCALARBYTES = 32;
const SODIUM_CRYPTO_SHORTHASH_BYTES = 8;
const SODIUM_CRYPTO_SHORTHASH_KEYBYTES = 16;
const SODIUM_CRYPTO_SECRETBOX_KEYBYTES = 32;
const SODIUM_CRYPTO_SECRETBOX_MACBYTES = 16;
const SODIUM_CRYPTO_SECRETBOX_NONCEBYTES = 24;
const SODIUM_CRYPTO_SIGN_BYTES = 64;
const SODIUM_CRYPTO_SIGN_SEEDBYTES = 32;
const SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES = 32;
const SODIUM_CRYPTO_SIGN_SECRETKEYBYTES = 64;
const SODIUM_CRYPTO_SIGN_KEYPAIRBYTES = 96;
const SODIUM_CRYPTO_STREAM_KEYBYTES = 32;
const SODIUM_CRYPTO_STREAM_NONCEBYTES = 24;
const SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE = 2;
const SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE = 67108864;
const SODIUM_CRYPTO_PWHASH_OPSLIMIT_MODERATE = 3;
const SODIUM_CRYPTO_PWHASH_MEMLIMIT_MODERATE = 268435456;
const SODIUM_CRYPTO_PWHASH_OPSLIMIT_SENSITIVE = 4;
const SODIUM_CRYPTO_PWHASH_MEMLIMIT_SENSITIVE = 1073741824;
const SODIUM_LIBRARY_VERSION = "1.0.18";
const SODIUM_LIBRARY_MAJOR_VERSION = 10;
const SODIUM_LIBRARY_MINOR_VERSION = 3;
const SODIUM_CRYPTO_KDF_BYTES_MIN = 16;
const SODIUM_CRYPTO_KDF_BYTES_MAX = 64;
const SODIUM_CRYPTO_KDF_CONTEXTBYTES = 8;
const SODIUM_CRYPTO_KDF_KEYBYTES = 32;
const SODIUM_CRYPTO_KX_SEEDBYTES = 32;
const SODIUM_CRYPTO_KX_SESSIONKEYBYTES = 32;
const SODIUM_CRYPTO_KX_KEYPAIRBYTES = 64;
const SODIUM_CRYPTO_PWHASH_ALG_ARGON2I13 = 1;
const SODIUM_CRYPTO_PWHASH_ALG_DEFAULT = 2;
const SODIUM_CRYPTO_PWHASH_SALTBYTES = 16;
const SODIUM_CRYPTO_PWHASH_STRPREFIX = '$argon2id$';
const SODIUM_CRYPTO_STREAM_XCHACHA20_NONCEBYTES = 24;
const SODIUM_CRYPTO_STREAM_XCHACHA20_KEYBYTES = 32;
const SODIUM_CRYPTO_SCALARMULT_RISTRETTO255_BYTES = 32;
const SODIUM_CRYPTO_SCALARMULT_RISTRETTO255_SCALARBYTES = 32;
const SODIUM_CRYPTO_CORE_RISTRETTO255_BYTES = 32;
const SODIUM_CRYPTO_CORE_RISTRETTO255_HASHBYTES = 64;
const SODIUM_CRYPTO_CORE_RISTRETTO255_SCALARBYTES = 32;
const SODIUM_CRYPTO_CORE_RISTRETTO255_NONREDUCEDSCALARBYTES = 64;

#[PhpStormStubsElementAvailable('8.1')]
function sodium_crypto_core_ristretto255_add(string $p, string $q): string {}

#[PhpStormStubsElementAvailable('8.1')]
function sodium_crypto_core_ristretto255_from_hash(string $s): string {}

#[PhpStormStubsElementAvailable('8.1')]
function sodium_crypto_core_ristretto255_is_valid_point(string $s): bool {}

#[PhpStormStubsElementAvailable('8.1')]
function sodium_crypto_core_ristretto255_random(): string {}

#[PhpStormStubsElementAvailable('8.1')]
function sodium_crypto_core_ristretto255_scalar_add(string $x, string $y): string {}

#[PhpStormStubsElementAvailable('8.1')]
function sodium_crypto_core_ristretto255_scalar_complement(string $s): string {}

#[PhpStormStubsElementAvailable('8.1')]
function sodium_crypto_core_ristretto255_scalar_invert(string $s): string {}

#[PhpStormStubsElementAvailable('8.1')]
function sodium_crypto_core_ristretto255_scalar_mul(string $x, string $y): string {}

#[PhpStormStubsElementAvailable('8.1')]
function sodium_crypto_core_ristretto255_scalar_negate(string $s): string {}

#[PhpStormStubsElementAvailable('8.1')]
function sodium_crypto_core_ristretto255_scalar_reduce(string $s): string {}

#[PhpStormStubsElementAvailable('8.1')]
function sodium_crypto_core_ristretto255_scalar_sub(string $x, string $y): string {}

#[PhpStormStubsElementAvailable('8.1')]
function sodium_crypto_core_ristretto255_scalar_random(): string {}

#[PhpStormStubsElementAvailable('8.1')]
function sodium_crypto_core_ristretto255_sub(string $p, string $q): string {}

#[PhpStormStubsElementAvailable('8.1')]
function sodium_crypto_scalarmult_ristretto255(string $n, string $p): string {}

#[PhpStormStubsElementAvailable('8.1')]
function sodium_crypto_scalarmult_ristretto255_base(string $n): string {}

#[PhpStormStubsElementAvailable('8.1')]
function sodium_crypto_stream_xchacha20(int $length, string $nonce, string $key): string {}

#[PhpStormStubsElementAvailable('8.1')]
function sodium_crypto_stream_xchacha20_xor(string $message, string $nonce, string $key): string {}

#[PhpStormStubsElementAvailable('8.1')]
function sodium_crypto_stream_xchacha20_keygen(): string {}








function sodium_crypto_aead_aes256gcm_is_available(): bool {}













function sodium_crypto_aead_aes256gcm_decrypt(string $ciphertext, string $additional_data, string $nonce, string $key): string|false {}













function sodium_crypto_aead_aes256gcm_encrypt(string $message, string $additional_data, string $nonce, string $key): string {}













function sodium_crypto_aead_chacha20poly1305_decrypt(string $ciphertext, string $additional_data, string $nonce, string $key): string|false {}













function sodium_crypto_aead_chacha20poly1305_encrypt(string $message, string $additional_data, string $nonce, string $key): string {}













function sodium_crypto_aead_chacha20poly1305_ietf_decrypt(string $ciphertext, string $additional_data, string $nonce, string $key): string|false {}













function sodium_crypto_aead_chacha20poly1305_ietf_encrypt(
string $message,
string $additional_data,
string $nonce,
string $key
): string {}











function sodium_crypto_auth(
string $message,
string $key
): string {}







function sodium_crypto_auth_keygen(): string {}








function sodium_crypto_kx_keypair(): string {}









function sodium_crypto_kx_publickey(string $key_pair): string {}








function sodium_crypto_kx_secretkey(string $key_pair): string {}









function sodium_crypto_kx_seed_keypair(string $seed): string {}










function sodium_crypto_kx_server_session_keys(string $server_key_pair, string $client_key): array {}







function sodium_crypto_generichash_keygen(): string {}









function sodium_crypto_kx_client_session_keys(string $client_key_pair, string $server_key): array {}











function sodium_crypto_kdf_derive_from_key(int $subkey_length, int $subkey_id, string $context, string $key): string {}







function sodium_crypto_kdf_keygen(): string {}







function sodium_crypto_shorthash_keygen(): string {}







function sodium_crypto_stream_keygen(): string {}










function sodium_pad(string $string, int $block_size): string {}










function sodium_unpad(string $string, int $block_size): string {}












function sodium_crypto_auth_verify(string $mac, string $message, string $key): bool {}












function sodium_crypto_box(string $message, string $nonce, string $key_pair): string {}








function sodium_crypto_box_keypair(): string {}









function sodium_crypto_box_seed_keypair(string $seed): string {}










function sodium_crypto_box_keypair_from_secretkey_and_publickey(string $secret_key, string $public_key): string {}












function sodium_crypto_box_open(string $ciphertext, string $nonce, string $key_pair): string|false {}









function sodium_crypto_box_publickey(string $key_pair): string {}









function sodium_crypto_box_publickey_from_secretkey(string $secret_key): string {}











function sodium_crypto_box_seal(string $message, string $public_key): string {}











function sodium_crypto_box_seal_open(string $ciphertext, string $key_pair): string|false {}









function sodium_crypto_box_secretkey(string $key_pair): string {}











function sodium_crypto_kx(
string $secret_key,
string $public_key,
string $client_publickey,
string $server_publickey
): string {}











function sodium_crypto_generichash(string $message, string $key = '', int $length = 32): string {}











function sodium_crypto_generichash_init(
string $key = '',
int $length = 32
): string {}











#[LanguageLevelTypeAware(['8.2' => 'true'], default: 'bool')]
function sodium_crypto_generichash_update(string &$state, string $message): bool {}











function sodium_crypto_generichash_final(
string &$state,
int $length = 32
): string {}















function sodium_crypto_pwhash(int $length, string $password, string $salt, int $opslimit, int $memlimit, int $algo = SODIUM_CRYPTO_PWHASH_ALG_DEFAULT): string {}












function sodium_crypto_pwhash_str(string $password, int $opslimit, int $memlimit): string {}











function sodium_crypto_pwhash_str_verify(string $hash, string $password): bool {}














function sodium_crypto_pwhash_scryptsalsa208sha256(
int $length,
string $password,
string $salt,
int $opslimit,
int $memlimit,
#[PhpStormStubsElementAvailable(from: '7.2', to: '7.4')] $alg = null
): string {}












function sodium_crypto_pwhash_scryptsalsa208sha256_str(string $password, int $opslimit, int $memlimit): string {}










function sodium_crypto_pwhash_scryptsalsa208sha256_str_verify(string $hash, string $password): bool {}











function sodium_crypto_scalarmult(string $n, string $p): string {}












function sodium_crypto_secretbox(string $message, string $nonce, string $key): string {}












function sodium_crypto_secretbox_open(string $ciphertext, string $nonce, string $key): string|false {}











function sodium_crypto_shorthash(string $message, string $key): string {}











function sodium_crypto_sign(string $message, string $secret_key): string {}











function sodium_crypto_sign_detached(string $message, string $secret_key): string {}









function sodium_crypto_sign_ed25519_pk_to_curve25519(string $public_key): string {}









function sodium_crypto_sign_ed25519_sk_to_curve25519(string $secret_key): string {}








function sodium_crypto_sign_keypair(): string {}










function sodium_crypto_sign_keypair_from_secretkey_and_publickey(
string $secret_key,
string $public_key
): string {}










function sodium_crypto_sign_open(string $signed_message, string $public_key): string|false {}









function sodium_crypto_sign_publickey(string $key_pair): string {}









function sodium_crypto_sign_secretkey(string $key_pair): string {}









function sodium_crypto_sign_publickey_from_secretkey(string $secret_key): string {}









function sodium_crypto_sign_seed_keypair(string $seed): string {}











function sodium_crypto_sign_verify_detached(string $signature, string $message, string $public_key): bool {}












function sodium_crypto_stream(
int $length,
string $nonce,
string $key
): string {}












function sodium_crypto_stream_xor(
string $message,
string $nonce,
string $key
): string {}









function sodium_randombytes_buf(int $length): string {}








function sodium_randombytes_random16(): int {}









function sodium_randombytes_uniform(int $upperBoundNonInclusive): int {}









function sodium_bin2hex(string $string): string {}










function sodium_compare(string $string1, string $string2): int {}










function sodium_hex2bin(string $string, string $ignore = ''): string {}









function sodium_increment(string &$string): void {}









function sodium_add(string &$string1, string $string2): void {}






function sodium_library_version_major(): int {}






function sodium_library_version_minor(): int {}










function sodium_memcmp(string $string1, string $string2): int {}








function sodium_memzero(string &$string): void {}







function sodium_version_string(): string {}









function sodium_crypto_scalarmult_base(
string $secret_key,
#[PhpStormStubsElementAvailable(from: '7.2', to: '7.4')] $string_2
): string {}










function sodium_crypto_secretbox_keygen(): string {}










function sodium_crypto_aead_aes256gcm_keygen(): string {}









function sodium_crypto_aead_chacha20poly1305_keygen(): string {}










function sodium_crypto_aead_chacha20poly1305_ietf_keygen(): string {}











function sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(string $ciphertext, string $additional_data, string $nonce, string $key): string|false {}











function sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(string $message, string $additional_data, string $nonce, string $key): string {}





function sodium_crypto_aead_xchacha20poly1305_ietf_keygen(): string {}









function sodium_crypto_pwhash_str_needs_rehash(string $password, int $opslimit, int $memlimit): bool {}





function sodium_crypto_secretstream_xchacha20poly1305_keygen(): string {}








function sodium_crypto_secretstream_xchacha20poly1305_init_push(string $key): array {}









function sodium_crypto_secretstream_xchacha20poly1305_init_pull(string $header, string $key): string {}







function sodium_crypto_secretstream_xchacha20poly1305_rekey(string &$state): void {}









function sodium_bin2base64(string $string, int $id): string {}










function sodium_base642bin(string $string, int $id, string $ignore = ''): string {}

class SodiumException extends Exception {}
