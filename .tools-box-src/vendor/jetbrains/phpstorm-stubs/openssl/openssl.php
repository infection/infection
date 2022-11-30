<?php


use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;









#[Deprecated(since: '8.0')]
function openssl_pkey_free(#[LanguageLevelTypeAware(["8.0" => "OpenSSLAsymmetricKey"], default: "resource")] $key): void {}













#[LanguageLevelTypeAware(["8.0" => "OpenSSLAsymmetricKey|false"], default: "resource|false")]
function openssl_pkey_new(?array $options) {}

















function openssl_pkey_export(
#[LanguageLevelTypeAware(['8.0' => 'OpenSSLAsymmetricKey|OpenSSLCertificate|array|string'], default: 'resource|array|string')] $key,
&$output,
?string $passphrase,
?array $options
): bool {}




















function openssl_pkey_export_to_file(
#[LanguageLevelTypeAware(['8.0' => 'OpenSSLAsymmetricKey|OpenSSLCertificate|array|string'], default: 'resource|array|string')] $key,
string $output_filename,
?string $passphrase,
?array $options
): bool {}




















#[LanguageLevelTypeAware(["8.0" => "OpenSSLAsymmetricKey|false"], default: "resource|false")]
function openssl_pkey_get_private(
#[LanguageLevelTypeAware(['8.0' => 'OpenSSLAsymmetricKey|OpenSSLCertificate|array|string'], default: 'resource|array|string')] $private_key,
?string $passphrase = null
) {}















#[LanguageLevelTypeAware(["8.0" => "OpenSSLAsymmetricKey|false"], default: "resource|false")]
function openssl_pkey_get_public(#[LanguageLevelTypeAware(['8.0' => 'OpenSSLAsymmetricKey|OpenSSLCertificate|array|string'], default: 'resource|array|string')] $public_key) {}




















function openssl_pkey_get_details(#[LanguageLevelTypeAware(["8.0" => "OpenSSLAsymmetricKey"], default: "resource")] $key): array|false {}







#[Deprecated(since: '8.0')]
function openssl_free_key(#[LanguageLevelTypeAware(["8.0" => "OpenSSLAsymmetricKey"], default: "resource")] $key): void {}




















#[LanguageLevelTypeAware(["8.0" => "OpenSSLAsymmetricKey|false"], default: "resource|false")]
function openssl_get_privatekey(
#[LanguageLevelTypeAware(['8.0' => 'OpenSSLAsymmetricKey|OpenSSLCertificate|array|string'], default: 'resource|array|string')] $private_key,
?string $passphrase
) {}
















#[LanguageLevelTypeAware(["8.0" => "OpenSSLAsymmetricKey|false"], default: "resource|false")]
function openssl_get_publickey(#[LanguageLevelTypeAware(['8.0' => 'OpenSSLAsymmetricKey|OpenSSLCertificate|array|string'], default: 'resource|array|string')] $public_key) {}
















function openssl_spki_new(#[LanguageLevelTypeAware(["8.0" => "OpenSSLAsymmetricKey"], default: "resource")] $private_key, string $challenge, int $digest_algo = 2): string|false {}








function openssl_spki_verify(string $spki): bool {}








function openssl_spki_export_challenge(string $spki): string|false {}








function openssl_spki_export(string $spki): string|false {}







#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificate|false"], default: "resource|false")]
function openssl_x509_read(#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificate|string"], default: "resource|string")] $certificate) {}








function openssl_x509_fingerprint(#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificate|string"], default: "resource|string")] $certificate, string $digest_algo = 'sha1', bool $binary = false): string|false {}






#[Deprecated(since: '8.0')]
function openssl_x509_free(#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificate"], default: "resource|string")] $certificate): void {}














function openssl_x509_parse(
#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificate|string"], default: "resource|string")] $certificate,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.0')] bool $shortname,
#[PhpStormStubsElementAvailable(from: '7.1')] bool $short_names = true
): array|false {}





























































function openssl_x509_checkpurpose(
#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificate|string"], default: "resource|string")] $certificate,
int $purpose,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.0')] array $ca_info,
#[PhpStormStubsElementAvailable(from: '7.1')] array $ca_info = [],
?string $untrusted_certificates_file
): int|bool {}













function openssl_x509_check_private_key(
#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificate|string"], default: "resource|string")] $certificate,
#[LanguageLevelTypeAware(['8.0' => 'OpenSSLAsymmetricKey|OpenSSLCertificate|array|string'], default: 'resource|array|string')] $private_key
): bool {}











function openssl_x509_export(#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificate|string"], default: "resource|string")] $certificate, &$output, bool $no_text = true): bool {}











function openssl_x509_export_to_file(#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificate|string"], default: "resource|string")] $certificate, string $output_filename, bool $no_text = true): bool {}









function openssl_x509_verify(
#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificate|string"], default: "resource|string")] $certificate,
#[LanguageLevelTypeAware(['8.0' => 'OpenSSLAsymmetricKey|OpenSSLCertificate|array|string'], default: 'resource|array|string')] $public_key
): int {}


















function openssl_pkcs12_export(
#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificate|string"], default: "resource|string")] $certificate,
&$output,
#[LanguageLevelTypeAware(['8.0' => 'OpenSSLAsymmetricKey|OpenSSLCertificate|array|string'], default: 'resource|array|string')] $private_key,
string $passphrase,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.0')] $args,
#[PhpStormStubsElementAvailable(from: '7.1')] array $options = []
): bool {}


















function openssl_pkcs12_export_to_file(#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificate|string"], default: "resource|string")] $certificate, string $output_filename, $private_key, string $passphrase, array $options = []): bool {}














function openssl_pkcs12_read(string $pkcs12, &$certificates, string $passphrase): bool {}






























































































#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificateSigningRequest|false"], default: "resource|false")]
function openssl_csr_new(
array $distinguished_names,
#[LanguageLevelTypeAware(['8.0' => 'OpenSSLAsymmetricKey'], default: 'resource')] &$private_key,
?array $options,
?array $extra_attributes
) {}









function openssl_csr_export(#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificateSigningRequest|string"], default: "resource|string")] $csr, &$output, bool $no_text = true): bool {}











function openssl_csr_export_to_file(#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificateSigningRequest|string"], default: "resource|string")] $csr, string $output_filename, bool $no_text = true): bool {}


































#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificate|false"], default: "resource|false")]
function openssl_csr_sign(
#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificateSigningRequest|string"], default: "resource|string")] $csr,
#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificate|string|null"], default: "resource|string|null")] $ca_certificate,
#[LanguageLevelTypeAware(["8.0" => "OpenSSLAsymmetricKey|OpenSSLCertificate|array|string"], default: "resource|array|string")] $private_key,
int $days,
?array $options,
int $serial = 0
) {}








function openssl_csr_get_subject(
#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificateSigningRequest|string"], default: "resource|string")] $csr,
#[PhpStormStubsElementAvailable(from: '7.1')] bool $short_names = true
): array|false {}








#[LanguageLevelTypeAware(["8.0" => "OpenSSLAsymmetricKey|false"], default: "resource|false")]
function openssl_csr_get_public_key(
#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificateSigningRequest|string"], default: "resource|string")] $csr,
#[PhpStormStubsElementAvailable(from: '7.1')] bool $short_names = true
) {}
















function openssl_digest(string $data, string $digest_algo, bool $binary = false): string|false {}


























function openssl_encrypt(
string $data,
string $cipher_algo,
string $passphrase,
int $options = 0,
string $iv = "",
#[PhpStormStubsElementAvailable(from: '7.1')] &$tag,
#[PhpStormStubsElementAvailable(from: '7.1')] string $aad = "",
#[PhpStormStubsElementAvailable(from: '7.1')] int $tag_length = 16
): string|false {}



























function openssl_decrypt(
string $data,
string $cipher_algo,
string $passphrase,
int $options = 0,
string $iv = "",
#[PhpStormStubsElementAvailable(from: '7.1')] #[LanguageLevelTypeAware(['8.1' => 'string|null'], default: 'string')] $tag = null,
#[PhpStormStubsElementAvailable(from: '7.1')] string $aad = ""
): string|false {}










function openssl_cipher_iv_length(string $cipher_algo): int|false {}















function openssl_sign(
string $data,
&$signature,
#[LanguageLevelTypeAware(['8.0' => 'OpenSSLAsymmetricKey|OpenSSLCertificate|array|string'], default: 'resource|array|string')] $private_key,
string|int $algorithm = OPENSSL_ALGO_SHA1
): bool {}













function openssl_verify(
string $data,
string $signature,
#[LanguageLevelTypeAware(['8.0' => 'OpenSSLAsymmetricKey|OpenSSLCertificate|array|string'], default: 'resource|array|string')] $public_key,
string|int $algorithm = OPENSSL_ALGO_SHA1
): int|false {}















function openssl_seal(
string $data,
&$sealed_data,
&$encrypted_keys,
array $public_key,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] string $cipher_algo = '',
#[PhpStormStubsElementAvailable(from: '8.0')] string $cipher_algo,
#[PhpStormStubsElementAvailable(from: '7.0')] &$iv = null
): int|false {}















function openssl_open(
string $data,
&$output,
string $encrypted_key,
#[LanguageLevelTypeAware(['8.0' => 'OpenSSLAsymmetricKey|OpenSSLCertificate|array|string'], default: 'resource|array|string')] $private_key,
#[PhpStormStubsElementAvailable(from: '7.0', to: '7.4')] string $cipher_algo = '',
#[PhpStormStubsElementAvailable(from: '8.0')] string $cipher_algo,
#[PhpStormStubsElementAvailable(from: '7.0')] ?string $iv
): bool {}












function openssl_pbkdf2(string $password, string $salt, int $key_length, int $iterations, string $digest_algo = 'sha1'): string|false {}





































function openssl_pkcs7_verify(
string $input_filename,
int $flags,
?string $signers_certificates_filename,
array $ca_info = [],
?string $untrusted_certificates_filename,
?string $content,
#[PhpStormStubsElementAvailable("7.2")] ?string $output_filename
): int|bool {}













function openssl_pkcs7_decrypt(
string $input_filename,
string $output_filename,
$certificate,
#[LanguageLevelTypeAware(['8.0' => 'OpenSSLAsymmetricKey|OpenSSLCertificate|array|string|null'], default: 'resource|array|string|null')] $private_key
): bool {}
























function openssl_pkcs7_sign(
string $input_filename,
string $output_filename,
#[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificate|string"], default: "resource|string")] $certificate,
#[LanguageLevelTypeAware(['8.0' => 'OpenSSLAsymmetricKey|OpenSSLCertificate|array|string'], default: 'resource|array|string')] $private_key,
?array $headers,
int $flags = PKCS7_DETACHED,
?string $untrusted_certificates_filename
): bool {}




























function openssl_pkcs7_encrypt(string $input_filename, string $output_filename, $certificate, ?array $headers, int $flags = 0, int $cipher_algo = OPENSSL_CIPHER_AES_128_CBC): bool {}














function openssl_private_encrypt(
string $data,
&$encrypted_data,
#[LanguageLevelTypeAware(['8.0' => 'OpenSSLAsymmetricKey|OpenSSLCertificate|array|string'], default: 'resource|array|string')] $private_key,
int $padding = OPENSSL_PKCS1_PADDING
): bool {}



















function openssl_private_decrypt(
string $data,
&$decrypted_data,
#[LanguageLevelTypeAware(['8.0' => 'OpenSSLAsymmetricKey|OpenSSLCertificate|array|string'], default: 'resource|array|string')] $private_key,
int $padding = OPENSSL_PKCS1_PADDING
): bool {}




















function openssl_public_encrypt(
string $data,
&$encrypted_data,
#[LanguageLevelTypeAware(['8.0' => 'OpenSSLAsymmetricKey|OpenSSLCertificate|array|string'], default: 'resource|array|string')] $public_key,
int $padding = OPENSSL_PKCS1_PADDING
): bool {}

















function openssl_public_decrypt(
string $data,
&$decrypted_data,
#[LanguageLevelTypeAware(['8.0' => 'OpenSSLAsymmetricKey|OpenSSLCertificate|array|string'], default: 'resource|array|string')] $public_key,
int $padding = OPENSSL_PKCS1_PADDING
): bool {}










function openssl_get_md_methods(bool $aliases = false): array {}










function openssl_get_cipher_methods(bool $aliases = false): array {}













function openssl_dh_compute_key(string $public_key, #[LanguageLevelTypeAware(["8.0" => "OpenSSLAsymmetricKey"], default: "resource")] $private_key): string|false {}








function openssl_pkey_derive(
$public_key,
#[LanguageLevelTypeAware(['8.0' => 'OpenSSLAsymmetricKey|OpenSSLCertificate|array|string'], default: 'resource|array|string')] $private_key,
int $key_length = 0
): string|false {}

















#[LanguageLevelTypeAware(["8.0" => "string"], default: "string|false")]
function openssl_random_pseudo_bytes(int $length, &$strong_result) {}







function openssl_error_string(): string|false {}







function openssl_get_cert_locations(): array {}

function openssl_get_curve_names(): array|false {}







function openssl_pkcs7_read(string $data, &$certificates): bool {}















function openssl_cms_verify(string $input_filename, int $flags = 0, ?string $certificates, array $ca_info = [], ?string $untrusted_certificates_filename, ?string $content, ?string $pk7, ?string $sigfile, int $encoding = OPENSSL_ENCODING_SMIME): bool {}













function openssl_cms_encrypt(string $input_filename, string $output_filename, $certificate, ?array $headers, int $flags = 0, int $encoding = OPENSSL_ENCODING_SMIME, int $cipher_algo = OPENSSL_CIPHER_AES_128_CBC): bool {}














function openssl_cms_sign(string $input_filename, string $output_filename, OpenSSLCertificate|string $certificate, $private_key, ?array $headers, int $flags = 0, int $encoding = OPENSSL_ENCODING_SMIME, ?string $untrusted_certificates_filename): bool {}











function openssl_cms_decrypt(string $input_filename, string $output_filename, $certificate, $private_key = null, int $encoding = OPENSSL_ENCODING_SMIME): bool {}








function openssl_cms_read(string $input_filename, &$certificates): bool {}

define('OPENSSL_VERSION_TEXT', "OpenSSL 1.0.0e 6 Sep 2011");
define('OPENSSL_VERSION_NUMBER', 268435551);
define('X509_PURPOSE_SSL_CLIENT', 1);
define('X509_PURPOSE_SSL_SERVER', 2);
define('X509_PURPOSE_NS_SSL_SERVER', 3);
define('X509_PURPOSE_SMIME_SIGN', 4);
define('X509_PURPOSE_SMIME_ENCRYPT', 5);
define('X509_PURPOSE_CRL_SIGN', 6);
define('X509_PURPOSE_ANY', 7);






define('OPENSSL_ALGO_SHA1', 1);
define('OPENSSL_ALGO_MD5', 2);
define('OPENSSL_ALGO_MD4', 3);
define('OPENSSL_ALGO_MD2', 4);
define('OPENSSL_ALGO_DSS1', 5);
define('OPENSSL_ALGO_SHA224', 6);
define('OPENSSL_ALGO_SHA256', 7);
define('OPENSSL_ALGO_SHA384', 8);
define('OPENSSL_ALGO_SHA512', 9);
define('OPENSSL_ALGO_RMD160', 10);










define('PKCS7_DETACHED', 64);








define('PKCS7_TEXT', 1);











define('PKCS7_NOINTERN', 16);






define('PKCS7_NOVERIFY', 32);






define('PKCS7_NOCHAIN', 8);










define('PKCS7_NOCERTS', 2);







define('PKCS7_NOATTR', 256);









define('PKCS7_BINARY', 128);





define('PKCS7_NOSIGS', 4);
define('OPENSSL_PKCS1_PADDING', 1);
define('OPENSSL_SSLV23_PADDING', 2);
define('OPENSSL_NO_PADDING', 3);
define('OPENSSL_PKCS1_OAEP_PADDING', 4);
define('OPENSSL_CIPHER_RC2_40', 0);
define('OPENSSL_CIPHER_RC2_128', 1);
define('OPENSSL_CIPHER_RC2_64', 2);
define('OPENSSL_CIPHER_DES', 3);
define('OPENSSL_CIPHER_3DES', 4);
define('OPENSSL_KEYTYPE_RSA', 0);
define('OPENSSL_KEYTYPE_DSA', 1);
define('OPENSSL_KEYTYPE_DH', 2);
define('OPENSSL_KEYTYPE_EC', 3);





define('OPENSSL_TLSEXT_SERVER_NAME', 1);




define('OPENSSL_CIPHER_AES_128_CBC', 5);

define('OPENSSL_CIPHER_AES_192_CBC', 6);

define('OPENSSL_CIPHER_AES_256_CBC', 7);
define('OPENSSL_RAW_DATA', 1);
define('OPENSSL_ZERO_PADDING', 2);
define('OPENSSL_DONT_ZERO_PAD_KEY', 4);




define('OPENSSL_CMS_DETACHED', 64);



define('OPENSSL_CMS_TEXT', 1);



define('OPENSSL_CMS_NOINTERN', 16);



define('OPENSSL_CMS_NOVERIFY', 32);



define('OPENSSL_CMS_NOCERTS', 2);



define('OPENSSL_CMS_NOATTR', 256);



define('OPENSSL_CMS_BINARY', 128);



define('OPENSSL_CMS_NOSIGS', 12);



define('OPENSSL_ENCODING_DER', 0);



define('OPENSSL_ENCODING_SMIME', 1);



define('OPENSSL_ENCODING_PEM', 2);

define('OPENSSL_DEFAULT_STREAM_CIPHERS', "ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES128-GCM-SHA256:" .
"ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:" .
"DHE-DSS-AES128-GCM-SHA256:kEDH+AESGCM:ECDHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES128-SHA256:" .
"ECDHE-RSA-AES128-SHA:ECDHE-ECDSA-AES128-SHA:ECDHE-RSA-AES256-SHA384:ECDHE-ECDSA-AES256-SHA384:" .
"ECDHE-RSA-AES256-SHA:ECDHE-ECDSA-AES256-SHA:DHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA:" .
"DHE-DSS-AES128-SHA256:DHE-RSA-AES256-SHA256:DHE-DSS-AES256-SHA:DHE-RSA-AES256-SHA:AES128-GCM-SHA256:" .
"AES256-GCM-SHA384:AES128:AES256:HIGH:!SSLv2:!aNULL:!eNULL:!EXPORT:!DES:!MD5:!RC4:!ADH");




final class OpenSSLCertificate
{




private function __construct() {}
}




final class OpenSSLCertificateSigningRequest
{




private function __construct() {}
}




final class OpenSSLAsymmetricKey
{




private function __construct() {}
}
