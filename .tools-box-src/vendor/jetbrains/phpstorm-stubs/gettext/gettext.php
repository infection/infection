<?php


use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Pure;











function textdomain(?string $domain): string {}










#[Pure]
function _(string $message): string {}










#[Pure]
function gettext(string $message): string {}












function dgettext(string $domain, string $message): string {}















function dcgettext(string $domain, string $message, int $category): string {}












function bindtextdomain(string $domain, #[LanguageLevelTypeAware(['8.0' => 'string|null'], default: 'string')] $directory): string|false {}











#[Pure]
function ngettext(string $singular, string $plural, int $count): string {}












#[Pure]
function dngettext(string $domain, string $singular, string $plural, int $count): string {}













#[Pure]
function dcngettext(string $domain, string $singular, string $plural, int $count, int $category): string {}












function bind_textdomain_codeset(string $domain, #[LanguageLevelTypeAware(['8.0' => 'string|null'], default: 'string')] $codeset): string|false {}


