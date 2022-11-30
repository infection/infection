<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\SodiumException;
function sodium_crypto_aead_aes256gcm_decrypt(string $ciphertext, string $additional_data, string $nonce, string $key) : string
{
    \error_clear_last();
    $result = \sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $additional_data, $nonce, $key);
    if ($result === \false) {
        throw SodiumException::createFromPhpError();
    }
    return $result;
}
function sodium_crypto_aead_chacha20poly1305_decrypt(string $ciphertext, string $additional_data, string $nonce, string $key) : string
{
    \error_clear_last();
    $result = \sodium_crypto_aead_chacha20poly1305_decrypt($ciphertext, $additional_data, $nonce, $key);
    if ($result === \false) {
        throw SodiumException::createFromPhpError();
    }
    return $result;
}
function sodium_crypto_aead_chacha20poly1305_encrypt(string $message, string $additional_data, string $nonce, string $key) : string
{
    \error_clear_last();
    $result = \sodium_crypto_aead_chacha20poly1305_encrypt($message, $additional_data, $nonce, $key);
    if ($result === \false) {
        throw SodiumException::createFromPhpError();
    }
    return $result;
}
function sodium_crypto_aead_chacha20poly1305_ietf_decrypt(string $ciphertext, string $additional_data, string $nonce, string $key) : string
{
    \error_clear_last();
    $result = \sodium_crypto_aead_chacha20poly1305_ietf_decrypt($ciphertext, $additional_data, $nonce, $key);
    if ($result === \false) {
        throw SodiumException::createFromPhpError();
    }
    return $result;
}
function sodium_crypto_aead_chacha20poly1305_ietf_encrypt(string $message, string $additional_data, string $nonce, string $key) : string
{
    \error_clear_last();
    $result = \sodium_crypto_aead_chacha20poly1305_ietf_encrypt($message, $additional_data, $nonce, $key);
    if ($result === \false) {
        throw SodiumException::createFromPhpError();
    }
    return $result;
}
function sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(string $ciphertext, string $additional_data, string $nonce, string $key) : string
{
    \error_clear_last();
    $result = \sodium_crypto_aead_xchacha20poly1305_ietf_decrypt($ciphertext, $additional_data, $nonce, $key);
    if ($result === \false) {
        throw SodiumException::createFromPhpError();
    }
    return $result;
}
function sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(string $message, string $additional_data, string $nonce, string $key) : string
{
    \error_clear_last();
    $result = \sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($message, $additional_data, $nonce, $key);
    if ($result === \false) {
        throw SodiumException::createFromPhpError();
    }
    return $result;
}
function sodium_crypto_auth_verify(string $mac, string $message, string $key) : void
{
    \error_clear_last();
    $result = \sodium_crypto_auth_verify($mac, $message, $key);
    if ($result === \false) {
        throw SodiumException::createFromPhpError();
    }
}
function sodium_crypto_box_open(string $ciphertext, string $nonce, string $key_pair) : string
{
    \error_clear_last();
    $result = \sodium_crypto_box_open($ciphertext, $nonce, $key_pair);
    if ($result === \false) {
        throw SodiumException::createFromPhpError();
    }
    return $result;
}
function sodium_crypto_box_seal_open(string $ciphertext, string $key_pair) : string
{
    \error_clear_last();
    $result = \sodium_crypto_box_seal_open($ciphertext, $key_pair);
    if ($result === \false) {
        throw SodiumException::createFromPhpError();
    }
    return $result;
}
function sodium_crypto_generichash_update(string &$state, string $message) : void
{
    \error_clear_last();
    $result = \sodium_crypto_generichash_update($state, $message);
    if ($result === \false) {
        throw SodiumException::createFromPhpError();
    }
}
function sodium_crypto_secretbox_open(string $ciphertext, string $nonce, string $key) : string
{
    \error_clear_last();
    $result = \sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
    if ($result === \false) {
        throw SodiumException::createFromPhpError();
    }
    return $result;
}
function sodium_crypto_sign_open(string $signed_message, string $public_key) : string
{
    \error_clear_last();
    $result = \sodium_crypto_sign_open($signed_message, $public_key);
    if ($result === \false) {
        throw SodiumException::createFromPhpError();
    }
    return $result;
}
function sodium_crypto_sign_verify_detached(string $signature, string $message, string $public_key) : void
{
    \error_clear_last();
    $result = \sodium_crypto_sign_verify_detached($signature, $message, $public_key);
    if ($result === \false) {
        throw SodiumException::createFromPhpError();
    }
}
