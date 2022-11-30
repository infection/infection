<?php


use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;













































#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection|false'], default: 'resource|false')]
function imap_open(string $mailbox, string $user, string $password, int $flags = 0, int $retries = 0, array $options = []) {}


















function imap_reopen(
#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap,
string $mailbox,
int $flags = 0,
int $retries = 0
): bool {}













function imap_close(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, int $flags = 0): bool {}







function imap_num_msg(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap): int|false {}








function imap_num_recent(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap): int {}








function imap_headers(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap): array|false {}

















































function imap_headerinfo(
#[LanguageLevelTypeAware(['8.0' => 'IMAP\Connection'], default: 'resource')] $imap,
int $message_num,
int $from_length = 0,
int $subject_length = 0,
#[PhpStormStubsElementAvailable(to: '7.4')] $default_host = null
): stdClass|false {}














function imap_rfc822_parse_headers(string $headers, string $default_hostname = "UNKNOWN"): stdClass {}
















function imap_rfc822_write_address(string $mailbox, string $hostname, string $personal): string|false {}


















function imap_rfc822_parse_adrlist(string $string, string $default_hostname): array {}














function imap_body(
#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap,
int $message_num,
int $flags = 0
): string|false {}















#[LanguageLevelTypeAware(['8.1' => 'stdClass|false'], default: 'object')]
function imap_bodystruct(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, int $message_num, string $section) {}


















function imap_fetchbody(
#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap,
int $message_num,
string $section,
int $flags = 0
): string|false {}



















function imap_fetchmime(
#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap,
int $message_num,
string $section,
int $flags = 0
): string|false {}






















function imap_savebody(
#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap,
$file,
int $message_num,
string $section = "",
int $flags = 0
): bool {}














function imap_fetchheader(
#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap,
int $message_num,
int $flags = 0
): string|false {}



























































































































function imap_fetchstructure(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, int $message_num, int $flags = 0): stdClass|false {}














function imap_gc(
#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] int $flags = 0,
#[PhpStormStubsElementAvailable(from: '8.0')] int $flags
): bool {}







function imap_expunge(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap): bool {}















function imap_delete(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $message_nums, int $flags = 0): bool {}











function imap_undelete(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $message_nums, int $flags = 0): bool {}
















function imap_check(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap): stdClass|false {}



























function imap_listscan(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $reference, string $pattern, string $content): array|false {}


















function imap_mail_copy(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $message_nums, string $mailbox, int $flags = 0): bool {}


















function imap_mail_move(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $message_nums, string $mailbox, int $flags = 0): bool {}





















function imap_mail_compose(array $envelope, array $bodies): string|false {}












function imap_createmailbox(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $mailbox): bool {}















function imap_renamemailbox(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $from, string $to): bool {}











function imap_deletemailbox(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $mailbox): bool {}











function imap_subscribe(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $mailbox): bool {}











function imap_unsubscribe(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $mailbox): bool {}


























function imap_append(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $folder, string $message, ?string $options = null, ?string $internal_date = null): bool {}







function imap_ping(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap): bool {}









function imap_base64(string $string): string|false {}









function imap_qprint(string $string): string|false {}









function imap_8bit(string $string): string|false {}









function imap_binary(string $string): string|false {}










function imap_utf8(string $mime_encoded_text): string {}






















#[LanguageLevelTypeAware(['8.1' => 'stdClass|false'], default: 'object')]
function imap_status(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $mailbox, int $flags) {}





function imap_status_current($stream_id, $options) {}













































function imap_mailboxmsginfo(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap): stdClass {}






















function imap_setflag_full(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $sequence, string $flag, int $options = NIL): bool {}





















function imap_clearflag_full(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $sequence, string $flag, int $options = 0): bool {}




















function imap_sort(
#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap,
int $criteria,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: 'int')] $reverse,
int $flags = 0,
?string $search_criteria = null,
?string $charset = null
): array|false {}










function imap_uid(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, int $message_num): int|false {}











function imap_msgno(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, int $message_uid): int {}























function imap_list(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $reference, string $pattern): array|false {}























function imap_lsub(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $reference, string $pattern): array|false {}



































function imap_fetch_overview(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $sequence, int $flags = 0): array|false {}







function imap_alerts(): array|false {}









function imap_errors(): array|false {}







function imap_last_error(): string|false {}























function imap_search(
#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap,
string $criteria,
int $flags = SE_FREE,
string $charset = ""
): array|false {}














function imap_utf7_decode(string $string): string|false {}











function imap_utf7_encode(string $string): string {}
















function imap_mime_header_decode(string $string): array|false {}























function imap_thread(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, int $flags = SE_FREE): array|false {}






















function imap_timeout(int $timeout_type, int $timeout = -1): int|bool {}



























function imap_get_quota(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $quota_root): array|false {}


















function imap_get_quotaroot(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $mailbox): array|false {}














function imap_set_quota(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $quota_root, int $mailbox_size): bool {}


















function imap_setacl(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $mailbox, string $user_id, string $rights): bool {}











function imap_getacl(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $mailbox): array|false {}





function imap_myrights($stream_id, $mailbox) {}








function imap_setannotation($stream_id, $mailbox, $entry, $attr, $value) {}







function imap_getannotation($stream_id, $mailbox, $entry, $attr) {}



























function imap_mail(string $to, string $subject, string $message, ?string $additional_headers = null, ?string $cc = null, ?string $bcc = null, ?string $return_path = null): bool {}

















































function imap_header($stream_id, $msg_no, $from_length = 0, $subject_length = 0, $default_host = null) {}









function imap_listmailbox(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $reference, string $pattern): array|false {}
















































function imap_getmailboxes(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $reference, string $pattern): array|false {}









function imap_scanmailbox(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $reference, string $pattern, string $content): array|false {}









function imap_listsubscribed(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $reference, string $pattern): array|false {}





































function imap_getsubscribed(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $reference, string $pattern): array|false {}












function imap_fetchtext(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, int $message_num, int $flags = 0): string|false {}









function imap_scan(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $reference, string $pattern, string $content): array|false {}







function imap_create(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $mailbox): bool {}








function imap_rename(#[LanguageLevelTypeAware(['8.1' => 'IMAP\Connection'], default: 'resource')] $imap, string $from, string $to): bool {}









function imap_mutf7_to_utf8(string $string): string|false {}









function imap_utf8_to_mutf7(string $string): string|false {}




define('NIL', 0);
define('IMAP_OPENTIMEOUT', 1);
define('IMAP_READTIMEOUT', 2);
define('IMAP_WRITETIMEOUT', 3);
define('IMAP_CLOSETIMEOUT', 4);
define('OP_DEBUG', 1);





define('OP_READONLY', 2);






define('OP_ANONYMOUS', 4);
define('OP_SHORTCACHE', 8);
define('OP_SILENT', 16);
define('OP_PROTOTYPE', 32);






define('OP_HALFOPEN', 64);
define('OP_EXPUNGE', 128);
define('OP_SECURE', 256);






define('CL_EXPUNGE', 32768);





define('FT_UID', 1);





define('FT_PEEK', 2);
define('FT_NOT', 4);





define('FT_INTERNAL', 8);
define('FT_PREFETCHTEXT', 32);





define('ST_UID', 1);
define('ST_SILENT', 2);
define('ST_SET', 4);





define('CP_UID', 1);






define('CP_MOVE', 2);





define('SE_UID', 1);
define('SE_FREE', 2);





define('SE_NOPREFETCH', 4);
define('SO_FREE', 8);
define('SO_NOSERVER', 8);
define('SA_MESSAGES', 1);
define('SA_RECENT', 2);
define('SA_UNSEEN', 4);
define('SA_UIDNEXT', 8);
define('SA_UIDVALIDITY', 16);
define('SA_ALL', 31);






define('LATT_NOINFERIORS', 1);






define('LATT_NOSELECT', 2);





define('LATT_MARKED', 4);






define('LATT_UNMARKED', 8);
define('LATT_REFERRAL', 16);
define('LATT_HASCHILDREN', 32);
define('LATT_HASNOCHILDREN', 64);






define('SORTDATE', 0);






define('SORTARRIVAL', 1);






define('SORTFROM', 2);






define('SORTSUBJECT', 3);






define('SORTTO', 4);






define('SORTCC', 5);






define('SORTSIZE', 6);
define('TYPETEXT', 0);
define('TYPEMULTIPART', 1);
define('TYPEMESSAGE', 2);
define('TYPEAPPLICATION', 3);
define('TYPEAUDIO', 4);
define('TYPEIMAGE', 5);
define('TYPEVIDEO', 6);
define('TYPEMODEL', 7);
define('TYPEOTHER', 8);
define('ENC7BIT', 0);
define('ENC8BIT', 1);
define('ENCBINARY', 2);
define('ENCBASE64', 3);
define('ENCQUOTEDPRINTABLE', 4);
define('ENCOTHER', 5);





define('IMAP_GC_ELT', 1);





define('IMAP_GC_ENV', 2);





define('IMAP_GC_TEXTS', 4);
