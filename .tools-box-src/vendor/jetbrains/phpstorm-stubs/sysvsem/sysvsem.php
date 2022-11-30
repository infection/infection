<?php


use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;





















#[LanguageLevelTypeAware(["8.0" => "SysvSemaphore|false"], default: "resource|false")]
function sem_get(int $key, int $max_acquire = 1, int $permissions = 0666, bool $auto_release = true) {}















function sem_acquire(#[LanguageLevelTypeAware(["8.0" => "SysvSemaphore"], default: "resource")] $semaphore, bool $non_blocking = false): bool {}










function sem_release(#[LanguageLevelTypeAware(["8.0" => "SysvSemaphore"], default: "resource")] $semaphore): bool {}










function sem_remove(#[LanguageLevelTypeAware(["8.0" => "SysvSemaphore"], default: "resource")] $semaphore): bool {}




final class SysvSemaphore
{




private function __construct() {}
}


