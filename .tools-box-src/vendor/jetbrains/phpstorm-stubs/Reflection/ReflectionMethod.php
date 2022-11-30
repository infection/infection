<?php

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;
use JetBrains\PhpStorm\Pure;







class ReflectionMethod extends ReflectionFunctionAbstract
{



#[Immutable]
public $name;




#[Immutable]
#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $class;




public const IS_STATIC = 16;




public const IS_PUBLIC = 1;




public const IS_PROTECTED = 2;




public const IS_PRIVATE = 4;




public const IS_ABSTRACT = 64;




public const IS_FINAL = 32;


















public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'object|string'], default: '')] $objectOrMethod,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $method = null
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
public function isPublic(): bool {}







#[Pure]
#[TentativeType]
public function isPrivate(): bool {}







#[Pure]
#[TentativeType]
public function isProtected(): bool {}







#[Pure]
#[TentativeType]
public function isAbstract(): bool {}







#[Pure]
#[TentativeType]
public function isFinal(): bool {}







#[Pure]
#[TentativeType]
public function isStatic(): bool {}







#[Pure]
#[TentativeType]
public function isConstructor(): bool {}







#[Pure]
#[TentativeType]
public function isDestructor(): bool {}









#[Pure]
#[TentativeType]
public function getClosure(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.3')] $object,
#[PhpStormStubsElementAvailable(from: '7.4')] #[LanguageLevelTypeAware(['8.0' => 'object|null'], default: '')] $object = null
): Closure {}


















#[Pure]
#[TentativeType]
public function getModifiers(): int {}















public function invoke($object, ...$args) {}













#[TentativeType]
public function invokeArgs(#[LanguageLevelTypeAware(['8.0' => 'object|null'], default: '')] $object, array $args): mixed {}








#[Pure]
#[TentativeType]
public function getDeclaringClass(): ReflectionClass {}








#[Pure]
#[TentativeType]
public function getPrototype(): ReflectionMethod {}









#[PhpStormStubsElementAvailable(to: "8.0")]
#[TentativeType]
public function setAccessible(#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $accessible): void {}









#[Pure]
#[PhpStormStubsElementAvailable(from: "8.1")]
#[TentativeType]
public function setAccessible(bool $accessible): void {}

#[PhpStormStubsElementAvailable(from: '8.2')]
public function hasPrototype(): bool {}
}
