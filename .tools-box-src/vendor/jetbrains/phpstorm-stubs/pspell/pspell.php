<?php


use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;

































#[LanguageLevelTypeAware(['8.1' => 'PSpell\Dictionary|false'], default: 'int|false')]
function pspell_new(string $language, string $spelling = "", string $jargon = "", string $encoding = "", int $mode = 0) {}




































#[LanguageLevelTypeAware(['8.1' => 'PSpell\Dictionary|false'], default: 'int|false')]
function pspell_new_personal(string $filename, string $language, string $spelling = "", string $jargon = "", string $encoding = "", int $mode = 0) {}










#[LanguageLevelTypeAware(['8.1' => 'PSpell\Dictionary|false'], default: 'int|false')]
function pspell_new_config(#[LanguageLevelTypeAware(['8.1' => 'PSpell\Config'], default: 'int')] $config) {}










function pspell_check(#[LanguageLevelTypeAware(['8.1' => 'PSpell\Dictionary'], default: 'int')] $dictionary, string $word): bool {}










function pspell_suggest(#[LanguageLevelTypeAware(['8.1' => 'PSpell\Dictionary'], default: 'int')] $dictionary, string $word): array|false {}
















function pspell_store_replacement(#[LanguageLevelTypeAware(['8.1' => 'PSpell\Dictionary'], default: 'int')] $dictionary, string $misspelled, string $correct): bool {}










function pspell_add_to_personal(#[LanguageLevelTypeAware(['8.1' => 'PSpell\Dictionary'], default: 'int')] $dictionary, string $word): bool {}










function pspell_add_to_session(#[LanguageLevelTypeAware(['8.1' => 'PSpell\Dictionary'], default: 'int')] $dictionary, string $word): bool {}







function pspell_clear_session(#[LanguageLevelTypeAware(['8.1' => 'PSpell\Dictionary'], default: 'int')] $dictionary): bool {}










function pspell_save_wordlist(#[LanguageLevelTypeAware(['8.1' => 'PSpell\Dictionary'], default: 'int')] $dictionary): bool {}




























#[LanguageLevelTypeAware(['8.1' => 'PSpell\Config'], default: 'int')]
function pspell_config_create(string $language, string $spelling = "", string $jargon = "", string $encoding = "") {}











function pspell_config_runtogether(#[LanguageLevelTypeAware(['8.1' => 'PSpell\Config'], default: 'int')] $config, bool $allow): bool {}












function pspell_config_mode(#[LanguageLevelTypeAware(['8.1' => 'PSpell\Config'], default: 'int')] $config, int $mode): bool {}










function pspell_config_ignore(#[LanguageLevelTypeAware(['8.1' => 'PSpell\Config'], default: 'int')] $config, int $min_length): bool {}











function pspell_config_personal(#[LanguageLevelTypeAware(['8.1' => 'PSpell\Config'], default: 'int')] $config, string $filename): bool {}








function pspell_config_dict_dir(#[LanguageLevelTypeAware(['8.1' => 'PSpell\Config'], default: 'int')] $config, string $directory): bool {}








function pspell_config_data_dir(#[LanguageLevelTypeAware(['8.1' => 'PSpell\Config'], default: 'int')] $config, string $directory): bool {}










function pspell_config_repl(#[LanguageLevelTypeAware(['8.1' => 'PSpell\Config'], default: 'int')] $config, string $filename): bool {}











function pspell_config_save_repl(#[LanguageLevelTypeAware(['8.1' => 'PSpell\Config'], default: 'int')] $config, bool $save): bool {}

define('PSPELL_FAST', 1);
define('PSPELL_NORMAL', 2);
define('PSPELL_BAD_SPELLERS', 3);
define('PSPELL_RUN_TOGETHER', 8);


