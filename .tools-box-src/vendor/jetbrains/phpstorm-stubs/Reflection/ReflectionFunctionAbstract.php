<?php

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;
use JetBrains\PhpStorm\Pure;







abstract class ReflectionFunctionAbstract implements Reflector
{



#[Immutable]
#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $name;







#[PhpStormStubsElementAvailable(from: "5.4", to: "8.0")]
final private function __clone(): void {}







#[PhpStormStubsElementAvailable(from: "8.1")]
private function __clone(): void {}







#[TentativeType]
public function inNamespace(): bool {}







#[Pure]
#[TentativeType]
public function isClosure(): bool {}







#[Pure]
#[TentativeType]
public function isDeprecated(): bool {}







#[Pure]
#[TentativeType]
public function isInternal(): bool {}







#[Pure]
#[TentativeType]
public function isUserDefined(): bool {}








#[Pure]
#[TentativeType]
public function isGenerator(): bool {}








#[Pure]
#[TentativeType]
public function isVariadic(): bool {}







#[Pure]
#[TentativeType]
public function getClosureThis(): ?object {}









#[Pure]
#[TentativeType]
public function getClosureScopeClass(): ?ReflectionClass {}







#[Pure]
#[TentativeType]
public function getDocComment(): string|false {}








#[Pure]
#[TentativeType]
public function getEndLine(): int|false {}








#[Pure]
#[TentativeType]
public function getExtension(): ?ReflectionExtension {}







#[Pure]
#[TentativeType]
public function getExtensionName(): string|false {}







#[Pure]
#[TentativeType]
public function getFileName(): string|false {}







#[Pure]
#[TentativeType]
public function getName(): string {}







#[Pure]
#[TentativeType]
public function getNamespaceName(): string {}








#[Pure]
#[TentativeType]
public function getNumberOfParameters(): int {}








#[Pure]
#[TentativeType]
public function getNumberOfRequiredParameters(): int {}







#[Pure]
#[TentativeType]
public function getParameters(): array {}









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
public function getReturnType(): ?ReflectionType {}







#[Pure]
#[TentativeType]
public function getShortName(): string {}







#[Pure]
#[TentativeType]
public function getStartLine(): int|false {}







#[Pure]
#[TentativeType]
public function getStaticVariables(): array {}







#[TentativeType]
public function returnsReference(): bool {}









#[TentativeType]
public function hasReturnType(): bool {}

/**
@template







*/
#[Pure]
public function getAttributes(?string $name = null, int $flags = 0): array {}

#[PhpStormStubsElementAvailable('8.1')]
#[Pure]
public function getClosureUsedVariables(): array {}

#[PhpStormStubsElementAvailable('8.1')]
#[Pure]
public function hasTentativeReturnType(): bool {}

#[PhpStormStubsElementAvailable('8.1')]
#[Pure]
public function getTentativeReturnType(): ?ReflectionType {}

#[PhpStormStubsElementAvailable('8.1')]
#[Pure]
#[TentativeType]
public function isStatic(): bool {}

#[PhpStormStubsElementAvailable(from: '5.3', to: '5.6')]
public function __toString() {}
}
