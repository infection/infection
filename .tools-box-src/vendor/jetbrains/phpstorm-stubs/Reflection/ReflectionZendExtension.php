<?php

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;
use JetBrains\PhpStorm\Pure;





class ReflectionZendExtension implements Reflector
{



#[Immutable]
#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $name;









public function __construct(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name) {}












public static function export($name, $return = false) {}








#[TentativeType]
public function __toString(): string {}








#[Pure]
#[TentativeType]
public function getName(): string {}








#[Pure]
#[TentativeType]
public function getVersion(): string {}








#[Pure]
#[TentativeType]
public function getAuthor(): string {}








#[Pure]
#[TentativeType]
public function getURL(): string {}








#[Pure]
#[TentativeType]
public function getCopyright(): string {}








#[PhpStormStubsElementAvailable(from: "5.4", to: "8.0")]
final private function __clone(): void {}








#[PhpStormStubsElementAvailable(from: "8.1")]
private function __clone(): void {}
}
