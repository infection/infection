<?php

use JetBrains\PhpStorm\Pure;

/**
@template


*/
class ReflectionAttribute implements Reflector
{








public const IS_INSTANCEOF = 2;





private function __construct() {}







#[Pure]
public function getName(): string {}







#[Pure]
public function getTarget(): int {}







#[Pure]
public function isRepeated(): bool {}







#[Pure]
public function getArguments(): array {}







public function newInstance(): object {}







private function __clone(): void {}

public function __toString(): string {}

public static function export() {}
}
