<?php

use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Pure;









class ReflectionReference
{



private function __construct() {}









public static function fromArrayElement(
array $array,
#[LanguageLevelTypeAware(['8.0' => 'string|int'], default: '')] $key
): ?ReflectionReference {}







#[Pure]
public function getId(): string {}






private function __clone(): void {}
}
