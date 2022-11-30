<?php

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;
use JetBrains\PhpStorm\Pure;







class ReflectionParameter implements Reflector
{



#[Immutable]
#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $name;










public function __construct($function, #[LanguageLevelTypeAware(['8.0' => 'string|int'], default: '')] $param) {}

/**
@removed









*/
#[Deprecated(since: '7.4')]
public static function export($function, $parameter, $return = false) {}







#[TentativeType]
public function __toString(): string {}







#[Pure]
#[TentativeType]
public function getName(): string {}







#[Pure]
#[TentativeType]
public function isPassedByReference(): bool {}









#[TentativeType]
public function canBePassedByValue(): bool {}








#[Pure]
#[TentativeType]
public function getDeclaringFunction(): ReflectionFunctionAbstract {}








#[Pure]
#[TentativeType]
public function getDeclaringClass(): ?ReflectionClass {}








#[Deprecated(reason: "Use ReflectionParameter::getType() and the ReflectionType APIs should be used instead.", since: "8.0")]
#[Pure]
#[TentativeType]
public function getClass(): ?ReflectionClass {}








#[TentativeType]
public function hasType(): bool {}









#[Pure]
#[LanguageLevelTypeAware(
[
'7.1' => 'ReflectionNamedType|null',
'8.0' => 'ReflectionNamedType|ReflectionUnionType|null',
'8.1' => 'ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null'
],
default: 'ReflectionType|null'
)]
#[TentativeType]
public function getType(): ?ReflectionType {}








#[Deprecated(reason: "Use ReflectionParameter::getType() and the ReflectionType APIs should be used instead.", since: "8.0")]
#[Pure]
#[TentativeType]
public function isArray(): bool {}










#[Deprecated(reason: "Use ReflectionParameter::getType() and the ReflectionType APIs should be used instead.", since: "8.0")]
#[Pure]
#[TentativeType]
public function isCallable(): bool {}








#[TentativeType]
public function allowsNull(): bool {}








#[Pure]
#[TentativeType]
public function getPosition(): int {}








#[Pure]
#[TentativeType]
public function isOptional(): bool {}








#[Pure]
#[TentativeType]
public function isDefaultValueAvailable(): bool {}









#[Pure]
#[TentativeType]
public function getDefaultValue(): mixed {}








#[Pure]
#[TentativeType]
public function isDefaultValueConstant(): bool {}









#[Pure]
#[TentativeType]
public function getDefaultValueConstantName(): ?string {}








#[Pure]
#[TentativeType]
public function isVariadic(): bool {}







#[Pure]
public function isPromoted(): bool {}

/**
@template







*/
#[Pure]
public function getAttributes(?string $name = null, int $flags = 0): array {}







#[PhpStormStubsElementAvailable(from: "5.4", to: "8.0")]
final private function __clone(): void {}







#[PhpStormStubsElementAvailable(from: "8.1")]
private function __clone(): void {}
}
