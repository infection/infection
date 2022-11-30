<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\ImapException;
function imap_8bit(string $string) : string
{
    \error_clear_last();
    $safeResult = \imap_8bit($string);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_append($imap, string $folder, string $message, string $options = null, string $internal_date = null) : void
{
    \error_clear_last();
    if ($internal_date !== null) {
        $safeResult = \imap_append($imap, $folder, $message, $options, $internal_date);
    } elseif ($options !== null) {
        $safeResult = \imap_append($imap, $folder, $message, $options);
    } else {
        $safeResult = \imap_append($imap, $folder, $message);
    }
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
}
function imap_base64(string $string) : string
{
    \error_clear_last();
    $safeResult = \imap_base64($string);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_binary(string $string) : string
{
    \error_clear_last();
    $safeResult = \imap_binary($string);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_body($imap, int $message_num, int $flags = 0) : string
{
    \error_clear_last();
    $safeResult = \imap_body($imap, $message_num, $flags);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_bodystruct($imap, int $message_num, string $section) : \stdClass
{
    \error_clear_last();
    $safeResult = \imap_bodystruct($imap, $message_num, $section);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_check($imap) : \stdClass
{
    \error_clear_last();
    $safeResult = \imap_check($imap);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_clearflag_full($imap, string $sequence, string $flag, int $options = 0) : void
{
    \error_clear_last();
    $safeResult = \imap_clearflag_full($imap, $sequence, $flag, $options);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
}
function imap_close($imap, int $flags = 0) : void
{
    \error_clear_last();
    $safeResult = \imap_close($imap, $flags);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
}
function imap_createmailbox($imap, string $mailbox) : void
{
    \error_clear_last();
    $safeResult = \imap_createmailbox($imap, $mailbox);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
}
function imap_deletemailbox($imap, string $mailbox) : void
{
    \error_clear_last();
    $safeResult = \imap_deletemailbox($imap, $mailbox);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
}
function imap_fetch_overview($imap, string $sequence, int $flags = 0) : array
{
    \error_clear_last();
    $safeResult = \imap_fetch_overview($imap, $sequence, $flags);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_fetchbody($imap, int $message_num, string $section, int $flags = 0) : string
{
    \error_clear_last();
    $safeResult = \imap_fetchbody($imap, $message_num, $section, $flags);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_fetchheader($imap, int $message_num, int $flags = 0) : string
{
    \error_clear_last();
    $safeResult = \imap_fetchheader($imap, $message_num, $flags);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_fetchmime($imap, int $message_num, string $section, int $flags = 0) : string
{
    \error_clear_last();
    $safeResult = \imap_fetchmime($imap, $message_num, $section, $flags);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_fetchstructure($imap, int $message_num, int $flags = 0) : \stdClass
{
    \error_clear_last();
    $safeResult = \imap_fetchstructure($imap, $message_num, $flags);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_gc($imap, int $flags) : void
{
    \error_clear_last();
    $safeResult = \imap_gc($imap, $flags);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
}
function imap_getacl($imap, string $mailbox) : array
{
    \error_clear_last();
    $safeResult = \imap_getacl($imap, $mailbox);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_getmailboxes($imap, string $reference, string $pattern) : array
{
    \error_clear_last();
    $safeResult = \imap_getmailboxes($imap, $reference, $pattern);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_getsubscribed($imap, string $reference, string $pattern) : array
{
    \error_clear_last();
    $safeResult = \imap_getsubscribed($imap, $reference, $pattern);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_headerinfo($imap, int $message_num, int $from_length = 0, int $subject_length = 0) : \stdClass
{
    \error_clear_last();
    $safeResult = \imap_headerinfo($imap, $message_num, $from_length, $subject_length);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_headers($imap) : array
{
    \error_clear_last();
    $safeResult = \imap_headers($imap);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_listscan($imap, string $reference, string $pattern, string $content) : array
{
    \error_clear_last();
    $safeResult = \imap_listscan($imap, $reference, $pattern, $content);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_lsub($imap, string $reference, string $pattern) : array
{
    \error_clear_last();
    $safeResult = \imap_lsub($imap, $reference, $pattern);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_mail_compose(array $envelope, array $bodies) : string
{
    \error_clear_last();
    $safeResult = \imap_mail_compose($envelope, $bodies);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_mail_copy($imap, string $message_nums, string $mailbox, int $flags = 0) : void
{
    \error_clear_last();
    $safeResult = \imap_mail_copy($imap, $message_nums, $mailbox, $flags);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
}
function imap_mail_move($imap, string $message_nums, string $mailbox, int $flags = 0) : void
{
    \error_clear_last();
    $safeResult = \imap_mail_move($imap, $message_nums, $mailbox, $flags);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
}
function imap_mail(string $to, string $subject, string $message, string $additional_headers = null, string $cc = null, string $bcc = null, string $return_path = null) : void
{
    \error_clear_last();
    if ($return_path !== null) {
        $safeResult = \imap_mail($to, $subject, $message, $additional_headers, $cc, $bcc, $return_path);
    } elseif ($bcc !== null) {
        $safeResult = \imap_mail($to, $subject, $message, $additional_headers, $cc, $bcc);
    } elseif ($cc !== null) {
        $safeResult = \imap_mail($to, $subject, $message, $additional_headers, $cc);
    } elseif ($additional_headers !== null) {
        $safeResult = \imap_mail($to, $subject, $message, $additional_headers);
    } else {
        $safeResult = \imap_mail($to, $subject, $message);
    }
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
}
function imap_mailboxmsginfo($imap) : \stdClass
{
    \error_clear_last();
    $safeResult = \imap_mailboxmsginfo($imap);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_mime_header_decode(string $string) : array
{
    \error_clear_last();
    $safeResult = \imap_mime_header_decode($string);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_mutf7_to_utf8(string $string) : string
{
    \error_clear_last();
    $safeResult = \imap_mutf7_to_utf8($string);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_num_msg($imap) : int
{
    \error_clear_last();
    $safeResult = \imap_num_msg($imap);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_open(string $mailbox, string $user, string $password, int $flags = 0, int $retries = 0, array $options = [])
{
    \error_clear_last();
    $safeResult = \imap_open($mailbox, $user, $password, $flags, $retries, $options);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_qprint(string $string) : string
{
    \error_clear_last();
    $safeResult = \imap_qprint($string);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_renamemailbox($imap, string $from, string $to) : void
{
    \error_clear_last();
    $safeResult = \imap_renamemailbox($imap, $from, $to);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
}
function imap_rfc822_write_address(?string $mailbox, ?string $hostname, ?string $personal) : string
{
    \error_clear_last();
    $safeResult = \imap_rfc822_write_address($mailbox, $hostname, $personal);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_savebody($imap, $file, int $message_num, string $section = "", int $flags = 0) : void
{
    \error_clear_last();
    $safeResult = \imap_savebody($imap, $file, $message_num, $section, $flags);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
}
function imap_set_quota($imap, string $quota_root, int $mailbox_size) : void
{
    \error_clear_last();
    $safeResult = \imap_set_quota($imap, $quota_root, $mailbox_size);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
}
function imap_setacl($imap, string $mailbox, string $user_id, string $rights) : void
{
    \error_clear_last();
    $safeResult = \imap_setacl($imap, $mailbox, $user_id, $rights);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
}
function imap_setflag_full($imap, string $sequence, string $flag, int $options = 0) : void
{
    \error_clear_last();
    $safeResult = \imap_setflag_full($imap, $sequence, $flag, $options);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
}
function imap_sort($imap, int $criteria, int $reverse, int $flags = 0, string $search_criteria = null, string $charset = null) : array
{
    \error_clear_last();
    if ($charset !== null) {
        $safeResult = \imap_sort($imap, $criteria, $reverse, $flags, $search_criteria, $charset);
    } elseif ($search_criteria !== null) {
        $safeResult = \imap_sort($imap, $criteria, $reverse, $flags, $search_criteria);
    } else {
        $safeResult = \imap_sort($imap, $criteria, $reverse, $flags);
    }
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_status($imap, string $mailbox, int $flags) : \stdClass
{
    \error_clear_last();
    $safeResult = \imap_status($imap, $mailbox, $flags);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_subscribe($imap, string $mailbox) : void
{
    \error_clear_last();
    $safeResult = \imap_subscribe($imap, $mailbox);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
}
function imap_thread($imap, int $flags = \SE_FREE) : array
{
    \error_clear_last();
    $safeResult = \imap_thread($imap, $flags);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_timeout(int $timeout_type, int $timeout = -1)
{
    \error_clear_last();
    $safeResult = \imap_timeout($timeout_type, $timeout);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
function imap_undelete($imap, string $message_nums, int $flags = 0) : void
{
    \error_clear_last();
    $safeResult = \imap_undelete($imap, $message_nums, $flags);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
}
function imap_unsubscribe($imap, string $mailbox) : void
{
    \error_clear_last();
    $safeResult = \imap_unsubscribe($imap, $mailbox);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
}
function imap_utf8_to_mutf7(string $string) : string
{
    \error_clear_last();
    $safeResult = \imap_utf8_to_mutf7($string);
    if ($safeResult === \false) {
        throw ImapException::createFromPhpError();
    }
    return $safeResult;
}
