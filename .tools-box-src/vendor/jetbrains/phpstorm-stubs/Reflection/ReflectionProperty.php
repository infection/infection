<?php

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;
use JetBrains\PhpStorm\Pure;







class ReflectionProperty implements Reflector
{



#[Immutable]
#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $name;




#[Immutable]
#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $class;





#[Immutable]
public bool $isReadonly;






public const IS_STATIC = 16;






public const IS_PUBLIC = 1;






public const IS_PROTECTED = 2;






public const IS_PRIVATE = 4;




public const IS_READONLY = 5;









public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'object|string'], default: '')] $class,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $property
) {}

/**
@removed









*/
#[Deprecated(since: '7.4')]
public static function export($class, $name, $return = false) {}







#[TentativeType]
public function __toString(): string {}







#[Pure]
#[TentativeType]
public function getName(): string {}











#[Pure]
#[TentativeType]
public function getValue(#[LanguageLevelTypeAware(['8.0' => 'object|null'], default: '')] $object = null): mixed {}











#[TentativeType]
public function setValue(
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $objectOrValue,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value = null
): void {}







#[Pure]
#[TentativeType]
public function isPublic(): bool {}







#[Pure]
#[TentativeType]
public function isPrivate(): bool {}







#[Pure]
#[TentativeType]
public function isProtected(): bool {}







#[Pure]
#[TentativeType]
public function isStatic(): bool {}








#[Pure]
#[TentativeType]
public function isDefault(): bool {}







#[Pure]
#[TentativeType]
public function getModifiers(): int {}







#[Pure]
#[TentativeType]
public function getDeclaringClass(): ReflectionClass {}







#[Pure]
#[TentativeType]
public function getDocComment(): string|false {}








#[PhpStormStubsElementAvailable(to: "8.0")]
#[TentativeType]
public function setAccessible(#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $accessible): void {}









#[Pure]
#[PhpStormStubsElementAvailable(from: "8.1")]
#[TentativeType]
public function setAccessible(bool $accessible): void {}









#[Pure]
#[LanguageLevelTypeAware(
[
'8.0' => 'ReflectionNamedType|ReflectionUnionType|null',
'8.1' => 'ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null'
],
default: 'ReflectionNamedType|null'
)]
#[TentativeType]
public function getType(): ?ReflectionType {}








#[TentativeType]
public function hasType(): bool {}










#[Pure]
#[TentativeType]
public function isInitialized(?object $object = null): bool {}







#[Pure]
public function isPromoted(): bool {}







#[PhpStormStubsElementAvailable(from: "5.4", to: "8.0")]
final private function __clone(): void {}







#[PhpStormStubsElementAvailable(from: "8.1")]
private function __clone(): void {}





public function hasDefaultValue(): bool {}





#[Pure]
#[TentativeType]
public function getDefaultValue(): mixed {}

/**
@template







*/
#[Pure]
public function getAttributes(?string $name = null, int $flags = 0): array {}





public function isReadOnly(): bool {}
}
