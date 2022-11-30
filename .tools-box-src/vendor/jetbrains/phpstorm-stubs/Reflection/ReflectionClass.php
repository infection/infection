<?php

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;
use JetBrains\PhpStorm\Pure;

/**
@template



*/
class ReflectionClass implements Reflector
{



#[Immutable]
#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $name;






public const IS_IMPLICIT_ABSTRACT = 16;






public const IS_EXPLICIT_ABSTRACT = 64;






public const IS_FINAL = 32;




public const IS_READONLY = 65536;









public function __construct(#[LanguageLevelTypeAware(['8.0' => 'object|string'], default: '')] $objectOrClass) {}

/**
@removed








*/
#[Deprecated(since: '7.4')]
public static function export($argument, $return = false) {}







#[TentativeType]
public function __toString(): string {}







#[Pure]
#[TentativeType]
public function getName(): string {}







#[Pure]
#[TentativeType]
public function isInternal(): bool {}







#[Pure]
#[TentativeType]
public function isUserDefined(): bool {}







#[Pure]
#[TentativeType]
public function isInstantiable(): bool {}








#[Pure]
#[TentativeType]
public function isCloneable(): bool {}









#[Pure]
#[TentativeType]
public function getFileName(): string|false {}







#[Pure]
#[TentativeType]
public function getStartLine(): int|false {}








#[Pure]
#[TentativeType]
public function getEndLine(): int|false {}







#[Pure]
#[TentativeType]
public function getDocComment(): string|false {}








#[Pure]
#[TentativeType]
public function getConstructor(): ?ReflectionMethod {}








#[TentativeType]
public function hasMethod(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name): bool {}









#[Pure]
#[TentativeType]
public function getMethod(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name): ReflectionMethod {}










#[Pure]
#[TentativeType]
public function getMethods(#[LanguageLevelTypeAware(['8.0' => 'int|null'], default: '')] $filter = null): array {}








#[TentativeType]
public function hasProperty(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name): bool {}









#[Pure]
#[TentativeType]
public function getProperty(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name): ReflectionProperty {}










#[Pure]
#[TentativeType]
public function getProperties(#[LanguageLevelTypeAware(['8.0' => 'int|null'], default: '')] $filter = null): array {}









#[Pure]
#[TentativeType]
public function getReflectionConstant(string $name): ReflectionClassConstant|false {}









#[Pure]
#[TentativeType]
public function getReflectionConstants(#[PhpStormStubsElementAvailable(from: '8.0')] ?int $filter = ReflectionClassConstant::IS_PUBLIC|ReflectionClassConstant::IS_PROTECTED|ReflectionClassConstant::IS_PRIVATE): array {}








#[TentativeType]
public function hasConstant(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name): bool {}









#[Pure]
#[TentativeType]
public function getConstants(#[PhpStormStubsElementAvailable(from: '8.0')] ?int $filter = ReflectionClassConstant::IS_PUBLIC|ReflectionClassConstant::IS_PROTECTED|ReflectionClassConstant::IS_PRIVATE): array {}









#[Pure]
#[TentativeType]
public function getConstant(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name): mixed {}








#[Pure]
#[TentativeType]
public function getInterfaces(): array {}







#[Pure]
#[TentativeType]
public function getInterfaceNames(): array {}








#[Pure]
#[TentativeType]
public function isAnonymous(): bool {}







#[Pure]
#[TentativeType]
public function isInterface(): bool {}









#[Pure]
#[TentativeType]
public function getTraits(): array {}









#[Pure]
#[TentativeType]
public function getTraitNames(): array {}










#[Pure]
#[TentativeType]
public function getTraitAliases(): array {}









#[Pure]
#[TentativeType]
public function isTrait(): bool {}







#[Pure]
#[TentativeType]
public function isAbstract(): bool {}







#[Pure]
#[TentativeType]
public function isFinal(): bool {}




#[Pure]
public function isReadOnly(): bool {}







#[Pure]
#[TentativeType]
public function getModifiers(): int {}








#[Pure]
#[TentativeType]
public function isInstance(#[LanguageLevelTypeAware(['8.0' => 'object'], default: '')] $object): bool {}












public function newInstance(...$args) {}











#[TentativeType]
public function newInstanceWithoutConstructor(): object {}












#[TentativeType]
public function newInstanceArgs(array $args = []): ?object {}








#[Pure]
#[TentativeType]
public function getParentClass(): ReflectionClass|false {}









#[Pure]
#[TentativeType]
public function isSubclassOf(#[LanguageLevelTypeAware(['8.0' => 'ReflectionClass|string'], default: '')] $class): bool {}








#[Pure]
#[TentativeType]
public function getStaticProperties(): ?array {}











#[Pure]
#[TentativeType]
public function getStaticPropertyValue(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $default = null
): mixed {}









#[TentativeType]
public function setStaticPropertyValue(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value
): void {}











#[Pure]
#[TentativeType]
public function getDefaultProperties(): array {}







#[Pure]
#[TentativeType]
public function isIterateable(): bool {}








#[Pure]
#[TentativeType]
public function isIterable(): bool {}








#[TentativeType]
public function implementsInterface(#[LanguageLevelTypeAware(['8.0' => 'ReflectionClass|string'], default: '')] $interface): bool {}








#[Pure]
#[TentativeType]
public function getExtension(): ?ReflectionExtension {}








#[Pure]
#[TentativeType]
public function getExtensionName(): string|false {}







#[TentativeType]
public function inNamespace(): bool {}







#[Pure]
#[TentativeType]
public function getNamespaceName(): string {}







#[Pure]
#[TentativeType]
public function getShortName(): string {}

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
public function isEnum(): bool {}

#[PhpStormStubsElementAvailable(from: '8.2')]
public function isReadOnly(): bool {}
}
