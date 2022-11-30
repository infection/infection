<?php

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;
use JetBrains\PhpStorm\Pure;






class ReflectionExtension implements Reflector
{



#[Immutable]
#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $name;








public function __construct(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name) {}

/**
@removed










*/
#[Deprecated(since: '7.4')]
public static function export($name, $return = false) {}








#[TentativeType]
public function __toString(): string {}







#[Pure]
#[TentativeType]
public function getName(): string {}







#[Pure]
#[TentativeType]
public function getVersion(): ?string {}









#[Pure]
#[TentativeType]
public function getFunctions(): array {}







#[Pure]
#[TentativeType]
public function getConstants(): array {}








#[Pure]
#[TentativeType]
public function getINIEntries(): array {}









#[Pure]
#[TentativeType]
public function getClasses(): array {}








#[Pure]
#[TentativeType]
public function getClassNames(): array {}








#[Pure]
#[TentativeType]
public function getDependencies(): array {}







#[TentativeType]
public function info(): void {}








#[Pure]
#[TentativeType]
public function isPersistent(): bool {}








#[Pure]
#[TentativeType]
public function isTemporary(): bool {}







#[PhpStormStubsElementAvailable(from: "5.4", to: "8.0")]
final private function __clone(): void {}







#[PhpStormStubsElementAvailable(from: "8.1")]
private function __clone(): void {}
}
