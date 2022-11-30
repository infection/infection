<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\PspellException;
function pspell_add_to_personal(int $dictionary, string $word) : void
{
    \error_clear_last();
    $safeResult = \pspell_add_to_personal($dictionary, $word);
    if ($safeResult === \false) {
        throw PspellException::createFromPhpError();
    }
}
function pspell_add_to_session(int $dictionary, string $word) : void
{
    \error_clear_last();
    $safeResult = \pspell_add_to_session($dictionary, $word);
    if ($safeResult === \false) {
        throw PspellException::createFromPhpError();
    }
}
function pspell_clear_session(int $dictionary) : void
{
    \error_clear_last();
    $safeResult = \pspell_clear_session($dictionary);
    if ($safeResult === \false) {
        throw PspellException::createFromPhpError();
    }
}
function pspell_config_create(string $language, string $spelling = "", string $jargon = "", string $encoding = "") : int
{
    \error_clear_last();
    $safeResult = \pspell_config_create($language, $spelling, $jargon, $encoding);
    if ($safeResult === \false) {
        throw PspellException::createFromPhpError();
    }
    return $safeResult;
}
function pspell_config_data_dir(int $config, string $directory) : void
{
    \error_clear_last();
    $safeResult = \pspell_config_data_dir($config, $directory);
    if ($safeResult === \false) {
        throw PspellException::createFromPhpError();
    }
}
function pspell_config_dict_dir(int $config, string $directory) : void
{
    \error_clear_last();
    $safeResult = \pspell_config_dict_dir($config, $directory);
    if ($safeResult === \false) {
        throw PspellException::createFromPhpError();
    }
}
function pspell_config_ignore(int $config, int $min_length) : void
{
    \error_clear_last();
    $safeResult = \pspell_config_ignore($config, $min_length);
    if ($safeResult === \false) {
        throw PspellException::createFromPhpError();
    }
}
function pspell_config_mode(int $config, int $mode) : void
{
    \error_clear_last();
    $safeResult = \pspell_config_mode($config, $mode);
    if ($safeResult === \false) {
        throw PspellException::createFromPhpError();
    }
}
function pspell_config_personal(int $config, string $filename) : void
{
    \error_clear_last();
    $safeResult = \pspell_config_personal($config, $filename);
    if ($safeResult === \false) {
        throw PspellException::createFromPhpError();
    }
}
function pspell_config_repl(int $config, string $filename) : void
{
    \error_clear_last();
    $safeResult = \pspell_config_repl($config, $filename);
    if ($safeResult === \false) {
        throw PspellException::createFromPhpError();
    }
}
function pspell_config_runtogether(int $config, bool $allow) : void
{
    \error_clear_last();
    $safeResult = \pspell_config_runtogether($config, $allow);
    if ($safeResult === \false) {
        throw PspellException::createFromPhpError();
    }
}
function pspell_config_save_repl(int $config, bool $save) : void
{
    \error_clear_last();
    $safeResult = \pspell_config_save_repl($config, $save);
    if ($safeResult === \false) {
        throw PspellException::createFromPhpError();
    }
}
function pspell_new_config(int $config) : int
{
    \error_clear_last();
    $safeResult = \pspell_new_config($config);
    if ($safeResult === \false) {
        throw PspellException::createFromPhpError();
    }
    return $safeResult;
}
function pspell_new_personal(string $filename, string $language, string $spelling = "", string $jargon = "", string $encoding = "", int $mode = 0) : int
{
    \error_clear_last();
    $safeResult = \pspell_new_personal($filename, $language, $spelling, $jargon, $encoding, $mode);
    if ($safeResult === \false) {
        throw PspellException::createFromPhpError();
    }
    return $safeResult;
}
function pspell_new(string $language, string $spelling = "", string $jargon = "", string $encoding = "", int $mode = 0) : int
{
    \error_clear_last();
    $safeResult = \pspell_new($language, $spelling, $jargon, $encoding, $mode);
    if ($safeResult === \false) {
        throw PspellException::createFromPhpError();
    }
    return $safeResult;
}
function pspell_save_wordlist(int $dictionary) : void
{
    \error_clear_last();
    $safeResult = \pspell_save_wordlist($dictionary);
    if ($safeResult === \false) {
        throw PspellException::createFromPhpError();
    }
}
function pspell_store_replacement(int $dictionary, string $misspelled, string $correct) : void
{
    \error_clear_last();
    $safeResult = \pspell_store_replacement($dictionary, $misspelled, $correct);
    if ($safeResult === \false) {
        throw PspellException::createFromPhpError();
    }
}
