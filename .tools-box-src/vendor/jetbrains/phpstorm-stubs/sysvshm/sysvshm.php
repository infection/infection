<?php


use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;

















#[LanguageLevelTypeAware(["8.0" => "SysvSharedMemory|false"], default: "resource|false")]
function shm_attach(int $key, ?int $size, int $permissions = 0666) {}










function shm_remove(#[LanguageLevelTypeAware(["8.0" => "SysvSharedMemory"], default: "resource")] $shm): bool {}










function shm_detach(#[LanguageLevelTypeAware(["8.0" => "SysvSharedMemory"], default: "resource")] $shm): bool {}



















function shm_put_var(#[LanguageLevelTypeAware(["8.0" => "SysvSharedMemory"], default: "resource")] $shm, int $key, mixed $value): bool {}












function shm_has_var(#[LanguageLevelTypeAware(["8.0" => "SysvSharedMemory"], default: "resource")] $shm, int $key): bool {}












function shm_get_var(#[LanguageLevelTypeAware(["8.0" => "SysvSharedMemory"], default: "resource")] $shm, int $key): mixed {}













function shm_remove_var(#[LanguageLevelTypeAware(["8.0" => "SysvSharedMemory"], default: "resource")] $shm, int $key): bool {}




final class SysvSharedMemory
{




private function __construct() {}
}


