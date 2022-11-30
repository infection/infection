<?php

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;
use JetBrains\PhpStorm\Pure;







class ReflectionClassConstant implements Reflector
{



#[Immutable]
#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $name;




#[Immutable]
#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $class;





#[Immutable]
public bool $isFinal;






public const IS_PUBLIC = 1;






public const IS_PROTECTED = 2;






public const IS_PRIVATE = 4;




public const IS_FINAL = 5;









public function __construct(#[LanguageLevelTypeAware(['8.0' => 'string|object'], default: '')] $class, string $constant) {}

/**
@removed







*/
#[Deprecated(since: '7.4')]
public static function export($class, $name, $return = false) {}








#[Pure]
#[TentativeType]
public function getDeclaringClass(): ReflectionClass {}








#[Pure]
#[TentativeType]
public function getDocComment(): string|false {}









#[Pure]
#[TentativeType]
public function getModifiers(): int {}








#[Pure]
#[TentativeType]
public function getName(): string {}








#[Pure]
#[TentativeType]
public function getValue(): mixed {}








#[Pure]
#[TentativeType]
public function isPrivate(): bool {}








#[Pure]
#[TentativeType]
public function isProtected(): bool {}








#[Pure]
#[TentativeType]
public function isPublic(): bool {}








public function __toString(): string {}

/**
@template







*/
#[Pure]
public function getAttributes(?string $name = null, int $flags = 0): array {}






#[PhpStormStubsElementAvailable(from: "5.4", to: "8.0")]
final private function __clone(): void {}






#[PhpStormStubsElementAvailable(from: "8.1")]
private function __clone(): void {}

#[PhpStormStubsElementAvailable('8.1')]
public function isEnumCase(): bool {}





public function isFinal(): bool {}
}
