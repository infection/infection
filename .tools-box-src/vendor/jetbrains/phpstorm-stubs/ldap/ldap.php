<?php


use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware as PhpVersionAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable as Available;
use LDAP\Result;












function ldap_exop_passwd(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
#[Available(from: '7.1', to: '7.1')] string $user = "",
#[Available(from: '7.2', to: '7.2')] string $user,
#[Available(from: '7.3')] string $user = "",
#[Available(from: '7.1', to: '7.1')] string $old_password = "",
#[Available(from: '7.2', to: '7.2')] string $old_password,
#[Available(from: '7.3')] string $old_password = "",
#[Available(from: '7.1', to: '7.1')] string $new_password = "",
#[Available(from: '7.2', to: '7.2')] string $new_password,
#[Available(from: '7.3')] string $new_password = "",
#[Available(from: '7.3')] &$controls = null
): string|bool {}










function ldap_exop_refresh(#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap, string $dn, int $ttl): int|false {}








function ldap_exop_whoami(#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap): string|false {}













#[PhpVersionAware(['8.1' => 'LDAP\Result|bool'], default: 'resource|bool')]
function ldap_exop(#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap, string $request_oid, ?string $request_data, #[PhpVersionAware(["8.0" => "null|array"], default: "array")] $controls = null, &$response_data, &$response_oid) {}











function ldap_parse_exop(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
#[PhpVersionAware(['8.1' => 'LDAP\Result'], default: 'resource')] $result,
#[Available(from: '7.2', to: '7.4')] &$response_data,
#[Available(from: '8.0')] &$response_data = null,
#[Available(from: '7.2', to: '7.4')] &$response_oid,
#[Available(from: '8.0')] &$response_oid = null
): bool {}







function ldap_8859_to_t61(string $value): string {}







function ldap_t61_to_8859(string $value): string {}
























#[PhpVersionAware(['8.1' => 'LDAP\Connection|false'], default: 'resource|false')]
function ldap_connect(?string $uri, int $port = 389) {}







function ldap_close(#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap): bool {}











function ldap_bind(#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap, ?string $dn, ?string $password): bool {}














#[PhpVersionAware(['8.1' => 'LDAP\Result|false'], default: 'resource|false')]
function ldap_bind_ext(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
?string $dn,
?string $password,
#[PhpVersionAware(["8.0" => "null|array"], default: "array")] $controls = null
) {}














function ldap_sasl_bind(#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap, $binddn = null, $password = null, $sasl_mech = null, $sasl_realm = null, $sasl_authc_id = null, $sasl_authz_id = null, $props = null): bool {}









function ldap_unbind(#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap): bool {}































































#[PhpVersionAware(['8.1' => 'LDAP\Result|array|false'], default: 'resource|false')]
function ldap_read(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
array|string $base,
array|string $filter,
array $attributes = [],
int $attributes_only = 0,
int $sizelimit = -1,
int $timelimit = -1,
int $deref = 0,
#[Available(from: '7.3')] #[PhpVersionAware(["8.0" => "null|array"], default: "array")] $controls = null
) {}

























































#[PhpVersionAware(['8.1' => 'LDAP\Result|array|false'], default: 'resource|false')]
function ldap_list(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
array|string $base,
array|string $filter,
array $attributes = [],
int $attributes_only = 0,
int $sizelimit = -1,
int $timelimit = -1,
int $deref = 0,
#[Available(from: '7.3')] #[PhpVersionAware(["8.0" => "null|array"], default: "array")] $controls = null
) {}





























































#[PhpVersionAware(['8.1' => 'LDAP\Result|array|false'], default: 'resource|false')]
function ldap_search(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
array|string $base,
array|string $filter,
array $attributes = [],
int $attributes_only = 0,
int $sizelimit = -1,
int $timelimit = -1,
int $deref = 0,
#[Available(from: '7.3')] #[PhpVersionAware(["8.0" => "null|array"], default: "array")] $controls = null
) {}







function ldap_free_result(
#[Available(from: '5.3', to: '8.0')] $ldap,
#[Available(from: '8.1')] Result $result
): bool {}












#[PhpVersionAware(["8.0" => "int"], default: "int|false")]
function ldap_count_entries(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
#[PhpVersionAware(['8.1' => 'LDAP\Result'], default: 'resource')] $result
) {}











#[PhpVersionAware(['8.1' => 'LDAP\ResultEntry|false'], default: 'resource|false')]
function ldap_first_entry(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
#[PhpVersionAware(['8.1' => 'LDAP\Result'], default: 'resource')] $result
) {}












#[PhpVersionAware(['8.1' => 'LDAP\ResultEntry|false'], default: 'resource|false')]
function ldap_next_entry(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
#[PhpVersionAware(['8.1' => 'LDAP\ResultEntry'], default: 'resource')] $entry
) {}



























function ldap_get_entries(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
#[PhpVersionAware(['8.1' => 'LDAP\Result'], default: 'resource')] $result
): array|false {}











function ldap_first_attribute(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
#[PhpVersionAware(['8.1' => 'LDAP\ResultEntry'], default: 'resource')] $entry
): string|false {}











function ldap_next_attribute(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
#[PhpVersionAware(['8.1' => 'LDAP\ResultEntry'], default: 'resource')] $entry
): string|false {}











function ldap_get_attributes(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
#[PhpVersionAware(['8.1' => 'LDAP\ResultEntry'], default: 'resource')] $entry
): array {}






















function ldap_get_values(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
#[PhpVersionAware(['8.1' => 'LDAP\ResultEntry'], default: 'resource')] $entry,
string $attribute
): array|false {}














function ldap_get_values_len(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
#[PhpVersionAware(['8.1' => 'LDAP\ResultEntry'], default: 'resource')] $entry,
string $attribute
): array|false {}










function ldap_get_dn(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
#[PhpVersionAware(['8.1' => 'LDAP\ResultEntry'], default: 'resource')] $entry
): string|false {}


















function ldap_explode_dn(string $dn, int $with_attrib): array|false {}









function ldap_dn2ufn(string $dn): string|false {}
























function ldap_add(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
string $dn,
array $entry,
#[Available(from: '7.3')] #[PhpVersionAware(["8.0" => "null|array"], default: "array")] $controls = null
): bool {}


























#[PhpVersionAware(['8.1' => 'LDAP\Result|false'], default: 'resource|false')]
function ldap_add_ext(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
string $dn,
array $entry,
#[PhpVersionAware(["8.0" => "null|array"], default: "array")] $controls = null
) {}













function ldap_delete(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
string $dn,
#[Available(from: '7.3')] #[PhpVersionAware(["8.0" => "null|array"], default: "array")] $controls = null
): bool {}















#[PhpVersionAware(['8.1' => 'LDAP\Result|false'], default: 'resource|false')]
function ldap_delete_ext(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
string $dn,
#[PhpVersionAware(["8.0" => "null|array"], default: "array")] $controls = null
) {}
















function ldap_modify(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
string $dn,
array $entry,
#[Available(from: '7.3')] #[PhpVersionAware(["8.0" => "null|array"], default: "array")] $controls = null
): bool {}














function ldap_mod_add(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
string $dn,
array $entry,
#[Available(from: '7.3')] #[PhpVersionAware(["8.0" => "null|array"], default: "array")] $controls = null
): bool {}
















#[PhpVersionAware(['8.1' => 'LDAP\Result|false'], default: 'resource|false')]
function ldap_mod_add_ext(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
string $dn,
array $entry,
#[PhpVersionAware(["8.0" => "null|array"], default: "array")] $controls = null
) {}














function ldap_mod_replace(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
string $dn,
array $entry,
#[Available(from: '7.3')] #[PhpVersionAware(["8.0" => "null|array"], default: "array")] $controls = null
): bool {}
















#[PhpVersionAware(['8.1' => 'LDAP\Result|false'], default: 'resource|false')]
function ldap_mod_replace_ext(#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap, string $dn, array $entry, #[PhpVersionAware(["8.0" => "null|array"], default: "array")] $controls = null) {}














function ldap_mod_del(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
string $dn,
array $entry,
#[Available(from: '7.3')] #[PhpVersionAware(["8.0" => "null|array"], default: "array")] $controls = null
): bool {}
















#[PhpVersionAware(['8.1' => 'LDAP\Result|false'], default: 'resource|false')]
function ldap_mod_del_ext(#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap, string $dn, array $entry, #[PhpVersionAware(["8.0" => "null|array"], default: "array")] $controls = null) {}










function ldap_errno(#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap): int {}









function ldap_err2str(int $errno): string {}









function ldap_error(#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap): string {}




















function ldap_compare(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
string $dn,
string $attribute,
string $value,
#[Available(from: '7.3')] #[PhpVersionAware(["8.0" => "null|array"], default: "array")] $controls = null
): int|bool {}

/**
@removed













*/
#[Deprecated(since: "7.0")]
function ldap_sort(#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap, $result, string $sortfilter): bool {}























function ldap_rename(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
string $dn,
string $new_rdn,
string $new_parent,
bool $delete_old_rdn,
#[Available(from: '7.3')] #[PhpVersionAware(["8.0" => "null|array"], default: "array")] $controls = null
): bool {}

























#[PhpVersionAware(['8.1' => 'LDAP\Result|false'], default: 'resource|false')]
function ldap_rename_ext(#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap, string $dn, string $new_rdn, string $new_parent, bool $delete_old_rdn, #[PhpVersionAware(["8.0" => "null|array"], default: "array")] $controls = null) {}







































































function ldap_get_option(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
int $option,
#[Available(from: '5.3', to: '7.4')] &$value,
#[Available(from: '8.0')] &$value = null
): bool {}




































































































function ldap_set_option(
#[PhpVersionAware(['8.1' => 'LDAP\Connection|null'], default: 'resource')] $ldap,
int $option,
$value
): bool {}








#[PhpVersionAware(['8.1' => 'LDAP\ResultEntry|false'], default: 'resource')]
function ldap_first_reference(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
#[PhpVersionAware(['8.1' => 'LDAP\Result'], default: 'resource')] $result
) {}








#[PhpVersionAware(['8.1' => 'LDAP\ResultEntry|false'], default: 'resource')]
function ldap_next_reference(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
#[PhpVersionAware(['8.1' => 'LDAP\ResultEntry'], default: 'resource')] $entry
) {}









function ldap_parse_reference(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
#[PhpVersionAware(['8.1' => 'LDAP\ResultEntry'], default: 'resource')] $entry,
&$referrals
): bool {}













function ldap_parse_result(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
#[PhpVersionAware(['8.1' => 'LDAP\Result'], default: 'resource')] $result,
&$error_code,
&$matched_dn,
&$error_message,
&$referrals,
#[Available(from: '7.3')] &$controls = null
): bool {}







function ldap_start_tls(#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap): bool {}








function ldap_set_rebind_proc(#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap, ?callable $callback): bool {}

/**
@removed



















*/
#[Deprecated(since: "7.4")]
function ldap_control_paged_result(#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap, int $pagesize, $iscritical = false, $cookie = ""): bool {}

/**
@removed














*/
#[Deprecated(since: "7.4")]
function ldap_control_paged_result_response(#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap, $result, &$cookie = null, &$estimated = null): bool {}









function ldap_escape(string $value, string $ignore = "", int $flags = 0): string {}










































































function ldap_modify_batch(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
string $dn,
array $modifications_info,
#[Available(from: '7.3')] #[PhpVersionAware(["8.0" => "null|array"], default: "array")] $controls = null
): bool {}







function ldap_count_references(
#[PhpVersionAware(['8.1' => 'LDAP\Connection'], default: 'resource')] $ldap,
#[PhpVersionAware(['8.1' => 'LDAP\Result'], default: 'resource')] $result
): int {}

define('LDAP_ESCAPE_FILTER', 1);
define('LDAP_ESCAPE_DN', 2);
define('LDAP_DEREF_NEVER', 0);
define('LDAP_DEREF_SEARCHING', 1);
define('LDAP_DEREF_FINDING', 2);
define('LDAP_DEREF_ALWAYS', 3);
define('LDAP_MODIFY_BATCH_REMOVE', 2);
define('LDAP_MODIFY_BATCH_ADD', 1);
define('LDAP_MODIFY_BATCH_REMOVE_ALL', 18);
define('LDAP_MODIFY_BATCH_REPLACE', 3);

define('LDAP_OPT_X_TLS_REQUIRE_CERT', 24582);
define('LDAP_OPT_X_TLS_NEVER', 0);
define('LDAP_OPT_X_TLS_HARD', 1);
define('LDAP_OPT_X_TLS_DEMAND', 2);
define('LDAP_OPT_X_TLS_ALLOW', 3);
define('LDAP_OPT_X_TLS_TRY', 4);
define('LDAP_OPT_X_TLS_CERTFILE', 24580);
define('LDAP_OPT_X_TLS_CIPHER_SUITE', 24584);
define('LDAP_OPT_X_TLS_KEYFILE', 24581);
define('LDAP_OPT_X_TLS_DHFILE', 24590);
define('LDAP_OPT_X_TLS_CRLFILE', 24592);
define('LDAP_OPT_X_TLS_RANDOM_FILE', 24585);
define('LDAP_OPT_X_TLS_CRLCHECK', 24587);
define('LDAP_OPT_X_TLS_CRL_NONE', 0);
define('LDAP_OPT_X_TLS_CRL_PEER', 1);
define('LDAP_OPT_X_TLS_CRL_ALL', 2);
define('LDAP_OPT_X_TLS_PROTOCOL_MIN', 24583);
define('LDAP_OPT_X_TLS_PROTOCOL_SSL2', 512);
define('LDAP_OPT_X_TLS_PROTOCOL_SSL3', 768);
define('LDAP_OPT_X_TLS_PROTOCOL_TLS1_0', 769);
define('LDAP_OPT_X_TLS_PROTOCOL_TLS1_1', 770);
define('LDAP_OPT_X_TLS_PROTOCOL_TLS1_2', 771);
define('LDAP_OPT_X_TLS_PACKAGE', 24593);
define('LDAP_OPT_X_KEEPALIVE_IDLE', 25344);
define('LDAP_OPT_X_KEEPALIVE_PROBES', 25345);
define('LDAP_OPT_X_KEEPALIVE_INTERVAL', 25346);
define('LDAP_OPT_X_SASL_USERNAME', 24844);
define('LDAP_OPT_X_SASL_NOCANON', 24843);





define('LDAP_OPT_DEREF', 2);











define('LDAP_OPT_SIZELIMIT', 3);








define('LDAP_OPT_TIMELIMIT', 4);






define('LDAP_OPT_NETWORK_TIMEOUT', 20485);





define('LDAP_OPT_PROTOCOL_VERSION', 17);
define('LDAP_OPT_ERROR_NUMBER', 49);






define('LDAP_OPT_REFERRALS', 8);
define('LDAP_OPT_RESTART', 9);
define('LDAP_OPT_HOST_NAME', 48);
define('LDAP_OPT_ERROR_STRING', 50);
define('LDAP_OPT_MATCHED_DN', 51);





define('LDAP_OPT_SERVER_CONTROLS', 18);





define('LDAP_OPT_CLIENT_CONTROLS', 19);





define('LDAP_OPT_DEBUG_LEVEL', 20481);
define('LDAP_OPT_X_SASL_MECH', 24832);
define('LDAP_OPT_X_SASL_REALM', 24833);
define('LDAP_OPT_X_SASL_AUTHCID', 24834);
define('LDAP_OPT_X_SASL_AUTHZID', 24835);






define('LDAP_OPT_X_TLS_CACERTDIR', 24579);






define('LDAP_OPT_X_TLS_CACERTFILE', 24578);

define('LDAP_MODIFY_BATCH_ATTRIB', 'attrib');
define('LDAP_MODIFY_BATCH_MODTYPE', 'modtype');
define('LDAP_MODIFY_BATCH_VALUES', 'values');
define('LDAP_OPT_TIMEOUT', 20482);
define('LDAP_OPT_DIAGNOSTIC_MESSAGE', 50);






define("LDAP_CONTROL_MANAGEDSAIT", "2.16.840.1.113730.3.4.2");






define("LDAP_CONTROL_PROXY_AUTHZ", "2.16.840.1.113730.3.4.18");






define("LDAP_CONTROL_SUBENTRIES", "1.3.6.1.4.1.4203.1.10.1");






define("LDAP_CONTROL_VALUESRETURNFILTER", "1.2.826.0.1.3344810.2.3");






define("LDAP_CONTROL_ASSERT", "1.3.6.1.1.12");






define("LDAP_CONTROL_PRE_READ", "1.3.6.1.1.13.1");






define("LDAP_CONTROL_POST_READ", "1.3.6.1.1.13.2");






define("LDAP_CONTROL_SORTREQUEST", "1.2.840.113556.1.4.473");






define("LDAP_CONTROL_SORTRESPONSE", "1.2.840.113556.1.4.474");






define("LDAP_CONTROL_PAGEDRESULTS", "1.2.840.113556.1.4.319");






define("LDAP_CONTROL_SYNC", "1.3.6.1.4.1.4203.1.9.1.1");






define("LDAP_CONTROL_SYNC_STATE", "1.3.6.1.4.1.4203.1.9.1.2");






define("LDAP_CONTROL_SYNC_DONE", "1.3.6.1.4.1.4203.1.9.1.3");






define("LDAP_CONTROL_DONTUSECOPY", "1.3.6.1.1.22");






define("LDAP_CONTROL_PASSWORDPOLICYREQUEST", "1.3.6.1.4.1.42.2.27.8.5.1");






define("LDAP_CONTROL_PASSWORDPOLICYRESPONSE", "1.3.6.1.4.1.42.2.27.8.5.1");






define("LDAP_CONTROL_X_INCREMENTAL_VALUES", "1.2.840.113556.1.4.802");






define("LDAP_CONTROL_X_DOMAIN_SCOPE", "1.2.840.113556.1.4.1339");






define("LDAP_CONTROL_X_PERMISSIVE_MODIFY", "1.2.840.113556.1.4.1413");






define("LDAP_CONTROL_X_SEARCH_OPTIONS", "1.2.840.113556.1.4.1340");






define("LDAP_CONTROL_X_TREE_DELETE", "1.2.840.113556.1.4.805");






define("LDAP_CONTROL_X_EXTENDED_DN", "1.2.840.113556.1.4.529");






define("LDAP_CONTROL_VLVREQUEST", "2.16.840.1.113730.3.4.9");






define("LDAP_CONTROL_VLVRESPONSE", "2.16.840.1.113730.3.4.10");




define("LDAP_EXOP_MODIFY_PASSWD", "1.3.6.1.4.1.4203.1.11.1");




define("LDAP_EXOP_REFRESH", "1.3.6.1.4.1.1466.101.119.1");




define("LDAP_EXOP_START_TLS", "1.3.6.1.4.1.1466.20037");




define("LDAP_EXOP_TURN", "1.3.6.1.1.19");




define("LDAP_EXOP_WHO_AM_I", "1.3.6.1.4.1.4203.1.11.3");




define('LDAP_CONTROL_AUTHZID_REQUEST', '2.16.840.1.113730.3.4.16');




define('LDAP_CONTROL_AUTHZID_RESPONSE', '2.16.840.1.113730.3.4.15');


