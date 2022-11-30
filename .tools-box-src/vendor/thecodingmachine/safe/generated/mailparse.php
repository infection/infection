<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\MailparseException;
function mailparse_msg_extract_part_file($mimemail, $filename, callable $callbackfunc = null) : string
{
    \error_clear_last();
    if ($callbackfunc !== null) {
        $safeResult = \mailparse_msg_extract_part_file($mimemail, $filename, $callbackfunc);
    } else {
        $safeResult = \mailparse_msg_extract_part_file($mimemail, $filename);
    }
    if ($safeResult === \false) {
        throw MailparseException::createFromPhpError();
    }
    return $safeResult;
}
function mailparse_msg_free($mimemail) : void
{
    \error_clear_last();
    $safeResult = \mailparse_msg_free($mimemail);
    if ($safeResult === \false) {
        throw MailparseException::createFromPhpError();
    }
}
function mailparse_msg_parse_file(string $filename)
{
    \error_clear_last();
    $safeResult = \mailparse_msg_parse_file($filename);
    if ($safeResult === \false) {
        throw MailparseException::createFromPhpError();
    }
    return $safeResult;
}
function mailparse_msg_parse($mimemail, string $data) : void
{
    \error_clear_last();
    $safeResult = \mailparse_msg_parse($mimemail, $data);
    if ($safeResult === \false) {
        throw MailparseException::createFromPhpError();
    }
}
function mailparse_stream_encode($sourcefp, $destfp, string $encoding) : void
{
    \error_clear_last();
    $safeResult = \mailparse_stream_encode($sourcefp, $destfp, $encoding);
    if ($safeResult === \false) {
        throw MailparseException::createFromPhpError();
    }
}
