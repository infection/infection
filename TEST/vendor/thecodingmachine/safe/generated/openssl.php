<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\OpensslException;
function openssl_cipher_iv_length(string $cipher_algo) : int
{
    \error_clear_last();
    $result = \openssl_cipher_iv_length($cipher_algo);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_cms_decrypt(string $input_filename, string $output_filename, $certificate, $private_key = null, int $encoding = \OPENSSL_ENCODING_SMIME) : void
{
    \error_clear_last();
    if ($encoding !== \OPENSSL_ENCODING_SMIME) {
        $result = \openssl_cms_decrypt($input_filename, $output_filename, $certificate, $private_key, $encoding);
    } elseif ($private_key !== null) {
        $result = \openssl_cms_decrypt($input_filename, $output_filename, $certificate, $private_key);
    } else {
        $result = \openssl_cms_decrypt($input_filename, $output_filename, $certificate);
    }
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_cms_encrypt(string $input_filename, string $output_filename, $certificate, $headers, int $flags = 0, int $encoding = \OPENSSL_ENCODING_SMIME, int $cipher_algo = \OPENSSL_CIPHER_AES_128_CBC) : void
{
    \error_clear_last();
    $result = \openssl_cms_encrypt($input_filename, $output_filename, $certificate, $headers, $flags, $encoding, $cipher_algo);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_cms_read(string $input_filename, array &$certificates) : void
{
    \error_clear_last();
    $result = \openssl_cms_read($input_filename, $certificates);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_cms_sign(string $input_filename, string $output_filename, $certificate, $private_key, $headers, int $flags = 0, int $encoding = \OPENSSL_ENCODING_SMIME, $untrusted_certificates_filename = null) : void
{
    \error_clear_last();
    if ($untrusted_certificates_filename !== null) {
        $result = \openssl_cms_sign($input_filename, $output_filename, $certificate, $private_key, $headers, $flags, $encoding, $untrusted_certificates_filename);
    } else {
        $result = \openssl_cms_sign($input_filename, $output_filename, $certificate, $private_key, $headers, $flags, $encoding);
    }
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_cms_verify(string $input_filename, int $flags = 0, $certificates = null, array $ca_info = [], $untrusted_certificates_filename = null, $content = null, $pk7 = null, $sigfile = null, int $encoding = \OPENSSL_ENCODING_SMIME) : void
{
    \error_clear_last();
    if ($encoding !== \OPENSSL_ENCODING_SMIME) {
        $result = \openssl_cms_verify($input_filename, $flags, $certificates, $ca_info, $untrusted_certificates_filename, $content, $pk7, $sigfile, $encoding);
    } elseif ($sigfile !== null) {
        $result = \openssl_cms_verify($input_filename, $flags, $certificates, $ca_info, $untrusted_certificates_filename, $content, $pk7, $sigfile);
    } elseif ($pk7 !== null) {
        $result = \openssl_cms_verify($input_filename, $flags, $certificates, $ca_info, $untrusted_certificates_filename, $content, $pk7);
    } elseif ($content !== null) {
        $result = \openssl_cms_verify($input_filename, $flags, $certificates, $ca_info, $untrusted_certificates_filename, $content);
    } elseif ($untrusted_certificates_filename !== null) {
        $result = \openssl_cms_verify($input_filename, $flags, $certificates, $ca_info, $untrusted_certificates_filename);
    } elseif ($ca_info !== []) {
        $result = \openssl_cms_verify($input_filename, $flags, $certificates, $ca_info);
    } elseif ($certificates !== null) {
        $result = \openssl_cms_verify($input_filename, $flags, $certificates);
    } else {
        $result = \openssl_cms_verify($input_filename, $flags);
    }
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_csr_export_to_file($csr, string $output_filename, bool $no_text = \true) : void
{
    \error_clear_last();
    $result = \openssl_csr_export_to_file($csr, $output_filename, $no_text);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_csr_export($csr, ?string &$output, bool $no_text = \true) : void
{
    \error_clear_last();
    $result = \openssl_csr_export($csr, $output, $no_text);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_csr_get_public_key($csr, bool $short_names = \true)
{
    \error_clear_last();
    $result = \openssl_csr_get_public_key($csr, $short_names);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_csr_get_subject($csr, bool $short_names = \true) : array
{
    \error_clear_last();
    $result = \openssl_csr_get_subject($csr, $short_names);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_csr_new(array $distinguished_names, &$private_key, array $options = null, array $extra_attributes = null)
{
    \error_clear_last();
    if ($extra_attributes !== null) {
        $result = \openssl_csr_new($distinguished_names, $private_key, $options, $extra_attributes);
    } elseif ($options !== null) {
        $result = \openssl_csr_new($distinguished_names, $private_key, $options);
    } else {
        $result = \openssl_csr_new($distinguished_names, $private_key);
    }
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_csr_sign($csr, $ca_certificate, $private_key, int $days, array $options = null, int $serial = 0)
{
    \error_clear_last();
    if ($serial !== 0) {
        $result = \openssl_csr_sign($csr, $ca_certificate, $private_key, $days, $options, $serial);
    } elseif ($options !== null) {
        $result = \openssl_csr_sign($csr, $ca_certificate, $private_key, $days, $options);
    } else {
        $result = \openssl_csr_sign($csr, $ca_certificate, $private_key, $days);
    }
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_decrypt(string $data, string $cipher_algo, string $passphrase, int $options = 0, string $iv = "", string $tag = null, string $aad = "") : string
{
    \error_clear_last();
    if ($aad !== "") {
        $result = \openssl_decrypt($data, $cipher_algo, $passphrase, $options, $iv, $tag, $aad);
    } elseif ($tag !== null) {
        $result = \openssl_decrypt($data, $cipher_algo, $passphrase, $options, $iv, $tag);
    } else {
        $result = \openssl_decrypt($data, $cipher_algo, $passphrase, $options, $iv);
    }
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_dh_compute_key(string $public_key, $private_key) : string
{
    \error_clear_last();
    $result = \openssl_dh_compute_key($public_key, $private_key);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_digest(string $data, string $digest_algo, bool $binary = \false) : string
{
    \error_clear_last();
    $result = \openssl_digest($data, $digest_algo, $binary);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_get_curve_names() : array
{
    \error_clear_last();
    $result = \openssl_get_curve_names();
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_open(string $data, ?string &$output, string $encrypted_key, $private_key, string $cipher_algo, string $iv = null) : void
{
    \error_clear_last();
    if ($iv !== null) {
        $result = \openssl_open($data, $output, $encrypted_key, $private_key, $cipher_algo, $iv);
    } else {
        $result = \openssl_open($data, $output, $encrypted_key, $private_key, $cipher_algo);
    }
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_pbkdf2(string $password, string $salt, int $key_length, int $iterations, string $digest_algo = "sha1") : string
{
    \error_clear_last();
    $result = \openssl_pbkdf2($password, $salt, $key_length, $iterations, $digest_algo);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_pkcs12_export_to_file($certificate, string $output_filename, $private_key, string $passphrase, array $options = []) : void
{
    \error_clear_last();
    $result = \openssl_pkcs12_export_to_file($certificate, $output_filename, $private_key, $passphrase, $options);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_pkcs12_export($certificate, ?string &$output, $private_key, string $passphrase, array $options = []) : void
{
    \error_clear_last();
    $result = \openssl_pkcs12_export($certificate, $output, $private_key, $passphrase, $options);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_pkcs12_read(string $pkcs12, ?array &$certificates, string $passphrase) : void
{
    \error_clear_last();
    $result = \openssl_pkcs12_read($pkcs12, $certificates, $passphrase);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_pkcs7_decrypt(string $input_filename, string $output_filename, $certificate, $private_key = null) : void
{
    \error_clear_last();
    if ($private_key !== null) {
        $result = \openssl_pkcs7_decrypt($input_filename, $output_filename, $certificate, $private_key);
    } else {
        $result = \openssl_pkcs7_decrypt($input_filename, $output_filename, $certificate);
    }
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_pkcs7_encrypt(string $input_filename, string $output_filename, $certificate, array $headers, int $flags = 0, int $cipher_algo = \OPENSSL_CIPHER_AES_128_CBC) : void
{
    \error_clear_last();
    $result = \openssl_pkcs7_encrypt($input_filename, $output_filename, $certificate, $headers, $flags, $cipher_algo);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_pkcs7_read(string $data, ?array &$certificates) : void
{
    \error_clear_last();
    $result = \openssl_pkcs7_read($data, $certificates);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_pkcs7_sign(string $input_filename, string $output_filename, $certificate, $private_key, array $headers, int $flags = \PKCS7_DETACHED, string $untrusted_certificates_filename = null) : void
{
    \error_clear_last();
    if ($untrusted_certificates_filename !== null) {
        $result = \openssl_pkcs7_sign($input_filename, $output_filename, $certificate, $private_key, $headers, $flags, $untrusted_certificates_filename);
    } else {
        $result = \openssl_pkcs7_sign($input_filename, $output_filename, $certificate, $private_key, $headers, $flags);
    }
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_pkey_derive($public_key, $private_key, int $key_length = 0) : string
{
    \error_clear_last();
    $result = \openssl_pkey_derive($public_key, $private_key, $key_length);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_pkey_export_to_file($key, string $output_filename, ?string $passphrase = null, array $options = null) : void
{
    \error_clear_last();
    if ($options !== null) {
        $result = \openssl_pkey_export_to_file($key, $output_filename, $passphrase, $options);
    } elseif ($passphrase !== null) {
        $result = \openssl_pkey_export_to_file($key, $output_filename, $passphrase);
    } else {
        $result = \openssl_pkey_export_to_file($key, $output_filename);
    }
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_pkey_export($key, ?string &$output, ?string $passphrase = null, array $options = null) : void
{
    \error_clear_last();
    if ($options !== null) {
        $result = \openssl_pkey_export($key, $output, $passphrase, $options);
    } elseif ($passphrase !== null) {
        $result = \openssl_pkey_export($key, $output, $passphrase);
    } else {
        $result = \openssl_pkey_export($key, $output);
    }
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_pkey_get_private(string $private_key, string $passphrase = null)
{
    \error_clear_last();
    if ($passphrase !== null) {
        $result = \openssl_pkey_get_private($private_key, $passphrase);
    } else {
        $result = \openssl_pkey_get_private($private_key);
    }
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_pkey_get_public($public_key)
{
    \error_clear_last();
    $result = \openssl_pkey_get_public($public_key);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_pkey_new(array $options = null)
{
    \error_clear_last();
    if ($options !== null) {
        $result = \openssl_pkey_new($options);
    } else {
        $result = \openssl_pkey_new();
    }
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_private_decrypt(string $data, ?string &$decrypted_data, $private_key, int $padding = \OPENSSL_PKCS1_PADDING) : void
{
    \error_clear_last();
    $result = \openssl_private_decrypt($data, $decrypted_data, $private_key, $padding);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_private_encrypt(string $data, ?string &$encrypted_data, $private_key, int $padding = \OPENSSL_PKCS1_PADDING) : void
{
    \error_clear_last();
    $result = \openssl_private_encrypt($data, $encrypted_data, $private_key, $padding);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_public_decrypt(string $data, ?string &$decrypted_data, $public_key, int $padding = \OPENSSL_PKCS1_PADDING) : void
{
    \error_clear_last();
    $result = \openssl_public_decrypt($data, $decrypted_data, $public_key, $padding);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_public_encrypt(string $data, ?string &$encrypted_data, $public_key, int $padding = \OPENSSL_PKCS1_PADDING) : void
{
    \error_clear_last();
    $result = \openssl_public_encrypt($data, $encrypted_data, $public_key, $padding);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_random_pseudo_bytes(int $length, ?bool &$strong_result = null) : string
{
    \error_clear_last();
    $result = \openssl_random_pseudo_bytes($length, $strong_result);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_seal(string $data, ?string &$sealed_data, ?array &$encrypted_keys, array $public_key, string $cipher_algo, ?string &$iv = null) : int
{
    \error_clear_last();
    $result = \openssl_seal($data, $sealed_data, $encrypted_keys, $public_key, $cipher_algo, $iv);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_sign(string $data, ?string &$signature, $private_key, $algorithm = \OPENSSL_ALGO_SHA1) : void
{
    \error_clear_last();
    $result = \openssl_sign($data, $signature, $private_key, $algorithm);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_spki_export_challenge(string $spki) : ?string
{
    \error_clear_last();
    $result = \openssl_spki_export_challenge($spki);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_spki_export(string $spki) : ?string
{
    \error_clear_last();
    $result = \openssl_spki_export($spki);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_spki_new($private_key, string $challenge, int $digest_algo = \OPENSSL_ALGO_MD5) : ?string
{
    \error_clear_last();
    $result = \openssl_spki_new($private_key, $challenge, $digest_algo);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_spki_verify(string $spki) : void
{
    \error_clear_last();
    $result = \openssl_spki_verify($spki);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_verify(string $data, string $signature, $public_key, $algorithm = \OPENSSL_ALGO_SHA1)
{
    \error_clear_last();
    $result = \openssl_verify($data, $signature, $public_key, $algorithm);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_x509_export_to_file($certificate, string $output_filename, bool $no_text = \true) : void
{
    \error_clear_last();
    $result = \openssl_x509_export_to_file($certificate, $output_filename, $no_text);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_x509_export($certificate, ?string &$output, bool $no_text = \true) : void
{
    \error_clear_last();
    $result = \openssl_x509_export($certificate, $output, $no_text);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
}
function openssl_x509_fingerprint($certificate, string $digest_algo = "sha1", bool $binary = \false) : string
{
    \error_clear_last();
    $result = \openssl_x509_fingerprint($certificate, $digest_algo, $binary);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function openssl_x509_read($certificate)
{
    \error_clear_last();
    $result = \openssl_x509_read($certificate);
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
