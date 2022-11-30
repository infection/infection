<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\MailparseException;
function mailparse_msg_extract_part_file($mimemail, $filename, callable $callbackfunc = null) : string
{
    \error_clear_last();
    if ($callbackfunc !== null) {
        $result = \mailparse_msg_extract_part_file($mimemail, $filename, $callbackfunc);
    } else {
        $result = \mailparse_msg_extract_part_file($mimemail, $filename);
    }
    if ($result === \false) {
        throw MailparseException::createFromPhpError();
    }
    return $result;
}
function mailparse_msg_free($mimemail) : void
{
    \error_clear_last();
    $result = \mailparse_msg_free($mimemail);
    if ($result === \false) {
        throw MailparseException::createFromPhpError();
    }
}
function mailparse_msg_parse_file(string $filename)
{
    \error_clear_last();
    $result = \mailparse_msg_parse_file($filename);
    if ($result === \false) {
        throw MailparseException::createFromPhpError();
    }
    return $result;
}
function mailparse_msg_parse($mimemail, string $data) : void
{
    \error_clear_last();
    $result = \mailparse_msg_parse($mimemail, $data);
    if ($result === \false) {
        throw MailparseException::createFromPhpError();
    }
}
function mailparse_stream_encode($sourcefp, $destfp, string $encoding) : void
{
    \error_clear_last();
    $result = \mailparse_stream_encode($sourcefp, $destfp, $encoding);
    if ($result === \false) {
        throw MailparseException::createFromPhpError();
    }
}
