<?php


use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;

























#[LanguageLevelTypeAware(["8.0" => "Shmop|false"], default: "resource|false")]
function shmop_open(int $key, string $mode, int $permissions, int $size) {}
















#[LanguageLevelTypeAware(["8.0" => "string"], default: "string|false")]
function shmop_read(#[LanguageLevelTypeAware(["8.0" => "Shmop"], default: "resource")] $shmop, int $offset, int $size) {}










#[Deprecated(since: '8.0')]
function shmop_close(#[LanguageLevelTypeAware(["8.0" => "Shmop"], default: "resource")] $shmop): void {}











function shmop_size(#[LanguageLevelTypeAware(["8.0" => "Shmop"], default: "resource")] $shmop): int {}


















#[LanguageLevelTypeAware(["8.0" => "int"], default: "int|false")]
function shmop_write(#[LanguageLevelTypeAware(["8.0" => "Shmop"], default: "resource")] $shmop, string $data, int $offset) {}










function shmop_delete(#[LanguageLevelTypeAware(["8.0" => "Shmop"], default: "resource")] $shmop): bool {}




final class Shmop {}


